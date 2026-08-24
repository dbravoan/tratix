<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Party;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Orchestrates the contract lifecycle: creation with party resolution,
 * clause generation, validation and reference numbering.
 */
class ContractService
{
    public function __construct(
        private readonly TransactionResolver $resolver,
        private readonly ClauseBuilder $clauseBuilder,
        private readonly ContractLegalValidator $validator,
        private readonly CountryLegalConfig $countryConfig,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $sellerData
     * @param  array<string, mixed>  $buyerData
     */
    public function create(array $data, array $sellerData, array $buyerData, ?int $userId = null): Contract
    {
        $sellerTokens = [];
        if (! empty($sellerData['id_card_front_token'])) {
            $sellerTokens['vendedor_anverso'] = $sellerData['id_card_front_token'];
        }
        if (! empty($sellerData['id_card_back_token'])) {
            $sellerTokens['vendedor_reverso'] = $sellerData['id_card_back_token'];
        }
        if (empty($sellerTokens) && ! empty($sellerData['id_card_token'])) {
            $sellerTokens['vendedor'] = $sellerData['id_card_token'];
        }

        $buyerTokens = [];
        if (! empty($buyerData['id_card_front_token'])) {
            $buyerTokens['comprador_anverso'] = $buyerData['id_card_front_token'];
        }
        if (! empty($buyerData['id_card_back_token'])) {
            $buyerTokens['comprador_reverso'] = $buyerData['id_card_back_token'];
        }
        if (empty($buyerTokens) && ! empty($buyerData['id_card_token'])) {
            $buyerTokens['comprador'] = $buyerData['id_card_token'];
        }

        unset(
            $sellerData['id_card_token'], $sellerData['id_card_front_token'], $sellerData['id_card_back_token'],
            $buyerData['id_card_token'], $buyerData['id_card_front_token'], $buyerData['id_card_back_token']
        );

        $sellerData['party_type'] ??= 'particular';
        $sellerData['role'] = 'vendedor';
        $sellerData['tax_id'] = ! empty(trim((string) ($sellerData['tax_id'] ?? ''))) ? $sellerData['tax_id'] : 'PENDIENTE';
        $sellerData['tax_id_country'] ??= ($data['country'] ?? 'ES');
        $sellerData['country'] ??= ($sellerData['tax_id_country'] ?? 'ES');
        $sellerData['full_name'] = ! empty(trim((string) ($sellerData['full_name'] ?? $sellerData['company_name'] ?? '')))
            ? ($sellerData['full_name'] ?? $sellerData['company_name'])
            : 'Parte Vendedora (Pendiente de datos)';
        $sellerData['address'] = ! empty(trim((string) ($sellerData['address'] ?? ''))) ? $sellerData['address'] : 'Pendiente de cumplimentar';
        $sellerData['postal_code'] = ! empty(trim((string) ($sellerData['postal_code'] ?? ''))) ? $sellerData['postal_code'] : '00000';
        $sellerData['city'] = ! empty(trim((string) ($sellerData['city'] ?? ''))) ? $sellerData['city'] : ($data['city'] ?? 'Madrid');

        $buyerData['party_type'] ??= 'particular';
        $buyerData['role'] = 'comprador';
        $buyerData['tax_id'] = ! empty(trim((string) ($buyerData['tax_id'] ?? ''))) ? $buyerData['tax_id'] : 'PENDIENTE';
        $buyerData['tax_id_country'] ??= ($data['country'] ?? 'ES');
        $buyerData['country'] ??= ($buyerData['tax_id_country'] ?? 'ES');
        $buyerData['full_name'] = ! empty(trim((string) ($buyerData['full_name'] ?? $buyerData['company_name'] ?? '')))
            ? ($buyerData['full_name'] ?? $buyerData['company_name'])
            : 'Parte Compradora (Pendiente de datos)';
        $buyerData['address'] = ! empty(trim((string) ($buyerData['address'] ?? ''))) ? $buyerData['address'] : 'Pendiente de cumplimentar';
        $buyerData['postal_code'] = ! empty(trim((string) ($buyerData['postal_code'] ?? ''))) ? $buyerData['postal_code'] : '00000';
        $buyerData['city'] = ! empty(trim((string) ($buyerData['city'] ?? ''))) ? $buyerData['city'] : ($data['city'] ?? 'Madrid');

        $seller = new Party($sellerData);
        $buyer = new Party($buyerData);

        $resolution = $this->resolver->resolve($seller, $buyer);

        $data['reference'] ??= $this->nextReference();
        $data['quantity'] = (int) ($data['quantity'] ?? 1);
        $data['transaction_type'] = $resolution['transaction_type'];
        $data['jurisdiction'] = $resolution['jurisdiction'];
        $data['applicable_law'] = $this->resolveApplicableLaw($seller, $buyer);
        $data['creator_role'] = in_array($data['creator_role'] ?? null, ['vendedor', 'comprador'], true)
            ? $data['creator_role']
            : 'comprador';
        $data['tax_amount'] ??= 0;
        $data['total_amount'] = round((float) ($data['price_amount'] ?? 0) + (float) $data['tax_amount'], 2);
        $data['legal_notes'] = $this->resolver->vatTreatmentNotes($resolution, $data['applicable_law']);
        $data['user_id'] = $userId;
        $data['access_token'] ??= Str::uuid()->toString();
        $data['access_token_expires_at'] ??= now()->addDays(30);

        $contract = Contract::create($data);

        $seller->contract_id = $contract->id;
        $buyer->contract_id = $contract->id;
        $seller->save();
        $buyer->save();

        $clauses = $this->clauseBuilder->build($contract, $seller, $buyer, $resolution);
        $contract->update(['clauses' => $clauses]);

        // Attach scanned ID cards (anverso / reverso) if uploaded during creation
        foreach ($sellerTokens as $label => $token) {
            $this->attachScannedIdCard($contract, $token, $label, $userId);
        }
        foreach ($buyerTokens as $label => $token) {
            $this->attachScannedIdCard($contract, $token, $label, $userId);
        }

        return $contract->fresh();
    }

    /**
     * Updates an existing contract and its parties while in draft/review.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $sellerData
     * @param  array<string, mixed>  $buyerData
     */
    public function update(Contract $contract, array $data, array $sellerData, array $buyerData, ?int $userId = null): Contract
    {
        if (! in_array($contract->status, ['borrador', 'en_revision'], true)) {
            throw new \DomainException('Solo se pueden editar contratos en estado borrador o en revisión.');
        }

        $sellerTokens = [];
        if (! empty($sellerData['id_card_front_token'])) {
            $sellerTokens['vendedor_anverso'] = $sellerData['id_card_front_token'];
        }
        if (! empty($sellerData['id_card_back_token'])) {
            $sellerTokens['vendedor_reverso'] = $sellerData['id_card_back_token'];
        }
        if (empty($sellerTokens) && ! empty($sellerData['id_card_token'])) {
            $sellerTokens['vendedor'] = $sellerData['id_card_token'];
        }

        $buyerTokens = [];
        if (! empty($buyerData['id_card_front_token'])) {
            $buyerTokens['comprador_anverso'] = $buyerData['id_card_front_token'];
        }
        if (! empty($buyerData['id_card_back_token'])) {
            $buyerTokens['comprador_reverso'] = $buyerData['id_card_back_token'];
        }
        if (empty($buyerTokens) && ! empty($buyerData['id_card_token'])) {
            $buyerTokens['comprador'] = $buyerData['id_card_token'];
        }

        unset(
            $sellerData['id_card_token'], $sellerData['id_card_front_token'], $sellerData['id_card_back_token'],
            $buyerData['id_card_token'], $buyerData['id_card_front_token'], $buyerData['id_card_back_token']
        );

        $seller = $contract->seller() ?? new Party(['role' => 'vendedor', 'contract_id' => $contract->id]);
        $buyer = $contract->buyer() ?? new Party(['role' => 'comprador', 'contract_id' => $contract->id]);

        $seller->fill($sellerData);
        $buyer->fill($buyerData);

        $resolution = $this->resolver->resolve($seller, $buyer);

        $data['quantity'] = (int) ($data['quantity'] ?? $contract->quantity ?? 1);
        $data['transaction_type'] = $resolution['transaction_type'];
        $data['jurisdiction'] = $resolution['jurisdiction'];
        $data['applicable_law'] = $this->resolveApplicableLaw($seller, $buyer);
        $data['creator_role'] = in_array($data['creator_role'] ?? null, ['vendedor', 'comprador'], true)
            ? $data['creator_role']
            : $contract->creator_role;
        $data['tax_amount'] ??= 0;
        $data['total_amount'] = round((float) ($data['price_amount'] ?? 0) + (float) $data['tax_amount'], 2);
        $data['legal_notes'] = $this->resolver->vatTreatmentNotes($resolution, $data['applicable_law']);

        $contract->update($data);
        $seller->save();
        $buyer->save();

        $clauses = $this->clauseBuilder->build($contract, $seller, $buyer, $resolution);
        $contract->update(['clauses' => $clauses]);

        // Attach scanned ID cards if newly provided
        foreach ($sellerTokens as $label => $token) {
            $this->attachScannedIdCard($contract, $token, $label, $userId);
        }
        foreach ($buyerTokens as $label => $token) {
            $this->attachScannedIdCard($contract, $token, $label, $userId);
        }

        AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => $userId,
            'event' => 'contract_updated',
            'actor' => 'usuario',
            'detail' => 'Contrato actualizado mediante el asistente de edición.',
            'happened_at' => now(),
        ]);

        return $contract->fresh(['parties', 'documents']);
    }

    public function attachScannedIdCard(Contract $contract, ?string $scanToken, string $role, ?int $userId = null): ?ContractDocument
    {
        if (! $scanToken) {
            return null;
        }

        $diskName = config('filesystems.documents_disk', 'local');
        $disk = Storage::disk($diskName);
        $tempScansDir = 'documents/temp_scans';

        // Check if already attached as a contract document
        $existingDoc = ContractDocument::where('contract_id', $contract->id)
            ->where('path', 'like', "%{$scanToken}%")
            ->first();
        if ($existingDoc) {
            return $existingDoc;
        }

        $files = $disk->files($tempScansDir);
        $matchedFile = null;

        foreach ($files as $file) {
            if (str_contains($file, $scanToken)) {
                $matchedFile = $file;
                break;
            }
        }

        if (! $matchedFile || ! $disk->exists($matchedFile)) {
            return null;
        }

        $ext = pathinfo($matchedFile, PATHINFO_EXTENSION) ?: 'png';
        $targetDir = "documents/{$contract->reference}";
        $targetPath = "{$targetDir}/id_card_{$role}_{$scanToken}.{$ext}";

        $disk->makeDirectory($targetDir);
        $disk->move($matchedFile, $targetPath);

        $requirementKey = match ($contract->contract_type) {
            'servicios' => 'cif_partes',
            'internacional' => 'vat_numbers',
            default => 'dni_partes',
        };

        $doc = ContractDocument::create([
            'contract_id' => $contract->id,
            'requirement_key' => $requirementKey,
            'filename' => "Documento_Identidad_{$role}.{$ext}",
            'path' => $targetPath,
            'mime' => $disk->mimeType($targetPath) ?? 'application/octet-stream',
            'size' => $disk->size($targetPath) ?? 0,
            'status' => 'uploaded',
            'uploaded_by_user_id' => $userId,
            'uploaded_at' => now(),
        ]);

        AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => $userId,
            'event' => 'id_card_attached',
            'actor' => 'sistema',
            'detail' => "Documento de identidad ({$role}) escaneado y adjuntado automáticamente.",
            'happened_at' => now(),
        ]);

        return $doc;
    }

    /**
     * Rebuild clauses after any party/contract change.
     */
    public function rebuildClauses(Contract $contract): Contract
    {
        $seller = $contract->seller();
        $buyer = $contract->buyer();

        if (! $seller || ! $buyer) {
            return $contract;
        }

        $resolution = $this->resolver->resolve($seller, $buyer);

        $contract->update([
            'transaction_type' => $resolution['transaction_type'],
            'jurisdiction' => $resolution['jurisdiction'],
            'applicable_law' => $this->resolveApplicableLaw($seller, $buyer),
            'legal_notes' => $this->resolver->vatTreatmentNotes($resolution, $contract->applicableLaw()),
        ]);

        $clauses = $this->clauseBuilder->build($contract, $seller, $buyer, $resolution);
        $contract->update(['clauses' => $clauses]);

        return $contract->fresh();
    }

    public function resolveApplicableLaw(Party $seller, Party $buyer): string
    {
        $sellerCountry = strtoupper($seller->country);
        $buyerCountry = strtoupper($buyer->country);

        if ($sellerCountry === $buyerCountry && $this->countryConfig->isSupported($sellerCountry)) {
            return $sellerCountry;
        }

        if ($this->countryConfig->isSupported($sellerCountry)) {
            return $sellerCountry;
        }

        return 'ES';
    }

    /**
     * @return array{contract: Contract, issues: array<int, array{level: string, field: string, message: string}>}
     */
    public function validate(Contract $contract): array
    {
        $contract->load('parties');

        $issues = $this->validator->validate($contract);

        return ['contract' => $contract->fresh(), 'issues' => $issues];
    }

    /**
     * Recomputes the SHA-256 of the stored final PDF and compares it against
     * the hash recorded at sealing time.
     *
     * @return array{valid: bool, detail: string}
     */
    public function verifyIntegrity(Contract $contract): array
    {
        if (! $contract->final_pdf_path || ! $contract->final_hash) {
            return ['valid' => false, 'detail' => 'El contrato no está sellado.'];
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($contract->final_pdf_path)) {
            return ['valid' => false, 'detail' => 'No se encuentra el archivo del PDF final.'];
        }

        $current = hash('sha256', $disk->get($contract->final_pdf_path));

        $valid = hash_equals($contract->final_hash, $current);

        return [
            'valid' => $valid,
            'detail' => $valid
                ? 'SHA-256 '.substr($current, 0, 16).'… coincide con el sellado.'
                : 'SHA-256 '.substr($current, 0, 16).'… NO coincide con el sellado.',
        ];
    }

    public function nextReference(): string
    {
        $year = now()->format('Y');
        $last = Contract::where('reference', 'like', "C-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $number = $last ? (int) Str::afterLast($last, '-') + 1 : 1;

        return sprintf('C-%s-%04d', $year, $number);
    }
}
