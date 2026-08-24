<?php

namespace App\Services;

use App\Mail\ContractSignedMail;
use App\Mail\PartySignedMail;
use App\Models\Consent;
use App\Models\Contract;
use App\Models\Party;
use App\Models\Signature;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Firma Electrónica Simple (eIDAS art. 25): canvas signature or explicit
 * "accept" click, captured with IP, User-Agent and timestamp. When every party
 * has signed, the contract is sealed (hash + evidence certificate).
 */
class SignatureService
{
    public function __construct(
        private readonly ContractWorkflowService $workflow,
        private readonly SealingService $sealing,
        private readonly ContractPdfService $pdf,
    ) {}

    public function ensureToken(Contract $contract): Contract
    {
        if (blank($contract->access_token)) {
            $expiresIn = $contract->user?->plan === 'pro' || $contract->user?->plan === 'business'
                ? now()->addDays(30)
                : now()->addDays(7);

            $contract->update([
                'access_token' => (string) Str::uuid(),
                'access_token_expires_at' => $expiresIn,
            ]);
        }

        return $contract->fresh();
    }

    public function signingLink(Contract $contract): string
    {
        $this->ensureToken($contract);

        return route('sign.show', $contract->access_token);
    }

    public function partyHasSigned(Contract $contract, string $role): bool
    {
        return $contract->signatures()->where('party_role', $role)->exists();
    }

    public function allPartiesSigned(Contract $contract): bool
    {
        $required = ['vendedor', 'comprador'];

        foreach ($required as $role) {
            if (! $this->partyHasSigned($contract, $role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Records a signature for a party on the frozen version.
     *
     * @param  array<string, mixed>  $data  ip, user_agent, latitude, longitude
     */
    public function sign(
        Contract $contract,
        string $role,
        string $signerName,
        string $signerEmail,
        string $signatureType,
        ?string $signatureImageDataUrl,
        string $consentText,
        array $data = [],
    ): Signature {
        if (! in_array($role, ['vendedor', 'comprador'], true)) {
            throw new \DomainException('Rol de firmante no válido.');
        }

        $version = $contract->latestVersion();
        if (! $version || $version->frozen_at === null) {
            throw new \DomainException('El contrato aún no tiene una versión congelada para firmar.');
        }

        if (! in_array($contract->status, ['lista_para_firma', 'en_firma', 'firmado'], true)) {
            throw new \DomainException('El contrato no está en fase de firma.');
        }

        if ($this->partyHasSigned($contract, $role)) {
            throw new \DomainException('Esta parte ya ha firmado el contrato.');
        }

        $imagePath = null;
        if ($signatureImageDataUrl && str_starts_with($signatureImageDataUrl, 'data:image/png;base64,')) {
            $imagePath = $this->storeSignatureImage($contract, $role, $signatureImageDataUrl);
        }

        $party = $contract->parties()->where('role', $role)->first();

        try {
            $signature = Signature::create([
                'contract_id' => $contract->id,
                'contract_version_id' => $version->id,
                'party_id' => $party?->id,
                'party_role' => $role,
                'signer_name' => $signerName,
                'signer_email' => $signerEmail,
                'signature_type' => $signatureType,
                'signature_image_path' => $imagePath,
                'signed_at' => now(),
                'ip' => $data['ip'] ?? null,
                'user_agent' => isset($data['user_agent']) ? substr((string) $data['user_agent'], 0, 500) : null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'consent_text' => $consentText,
                'otp_verified' => $data['otp_verified'] ?? false,
                'otp_verification_id' => $data['otp_verification_id'] ?? null,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Concurrent duplicate: another request already signed this role.
            throw new \DomainException('Esta parte ya ha firmado el contrato.');
        }

        Consent::create([
            'contract_id' => $contract->id,
            'signer_email' => $signerEmail,
            'role' => $role,
            'consent_type' => 'signing',
            'policy_version' => '1.0',
            'accepted_at' => now(),
            'ip' => $data['ip'] ?? null,
            'user_agent' => isset($data['user_agent']) ? substr((string) $data['user_agent'], 0, 500) : null,
        ]);

        $this->workflow->record($contract, 'signed', $role, null, "Firma de {$signerName} ({$role}).");

        if ($this->allPartiesSigned($contract->fresh())) {
            $this->completeContract($contract->fresh());
        } else {
            $this->notifyPartySigned($contract->fresh(), $signerName, $role, $signerEmail);
        }

        return $signature->fresh();
    }

    private function notifyPartySigned(Contract $contract, string $signerName, string $role, ?string $signerEmail): void
    {
        $contract->loadMissing(['user', 'parties']);
        $otherRole = $role === 'vendedor' ? 'comprador' : 'vendedor';
        $otherParty = $otherRole === 'vendedor' ? $contract->seller() : $contract->buyer();

        $signUrl = $contract->access_token
            ? route('sign.show', ['token' => $contract->access_token, 'role' => $otherRole])
            : route('dashboard');

        // Notify the other party if their email is known
        if ($otherParty && $otherParty->email) {
            try {
                Mail::to($otherParty->email)->queue(
                    new PartySignedMail($contract, $signerName, $role, $signUrl, true)
                );
            } catch (\Throwable $e) {
                Log::warning('signature.other_party_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        // Also notify creator if creator is not the signer and prefers notifications
        if ($contract->user && $contract->user->email && $contract->user->email !== $signerEmail && ($contract->user->notify_signatures ?? true)) {
            try {
                Mail::to($contract->user->email)->queue(
                    new PartySignedMail($contract, $signerName, $role, route('contracts.show', $contract), false)
                );
            } catch (\Throwable $e) {
                Log::warning('signature.creator_notify_failed', ['error' => $e->getMessage()]);
            }
        }
    }

    private function completeContract(Contract $contract): void
    {
        // Serialize the sealing under a row lock so concurrent final signatures
        // cannot seal the contract twice.
        DB::transaction(function () use ($contract) {
            $locked = Contract::query()->whereKey($contract->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'firmado') {
                return;
            }

            $contract = $this->workflow->transition($locked, 'firmado', 'system', detail: 'Todas las partes han firmado.');

            // Generate the final sealed PDF (with signature images + evidence).
            $this->sealing->seal($contract);
        });

        // Notify the creator so they can download the signed and sealed PDF.
        $this->ensureToken($contract);
        $contract->loadMissing(['user', 'signatures']);

        $creatorUrl = route('contracts.show', $contract);

        if ($contract->user && ($contract->user->notify_signatures ?? true)) {
            try {
                Mail::to($contract->user->email)->queue(
                    new ContractSignedMail($contract, $creatorUrl)
                );
            } catch (\Throwable $e) {
                Log::warning('signature.creator_signed_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        // Also email the counterparty so BOTH parties keep the sealed contract
        // (important for any future dispute). Counterparties access via their token download URL.
        $counterparty = $contract->counterpartySignature();
        $counterpartyUrl = $contract->access_token
            ? route('sign.download', $contract->access_token)
            : route('verify.public', $contract->reference);

        if ($counterparty && $counterparty->signer_email && $counterparty->signer_email !== $contract->user?->email) {
            try {
                Mail::to($counterparty->signer_email)->queue(
                    new ContractSignedMail($contract, $counterpartyUrl)
                );
            } catch (\Throwable $e) {
                Log::warning('signature.counterparty_notify_failed', [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function storeSignatureImage(Contract $contract, string $role, string $dataUrl): string
    {
        $binary = base64_decode(Str::after($dataUrl, 'data:image/png;base64,'));

        $relative = 'signatures/'.$contract->reference.'/'.$role.'-'.now()->format('YmdHis').'.png';
        Storage::disk('local')->put('contracts/'.$relative, $binary);

        return 'contracts/'.$relative;
    }
}
