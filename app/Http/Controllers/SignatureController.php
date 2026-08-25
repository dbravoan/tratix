<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Contract;
use App\Services\ContractPdfService;
use App\Services\ContractWorkflowService;
use App\Services\PartyRightsObligations;
use App\Services\SignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public signing endpoint (Firma Electrónica Simple). The signer opens a link,
 * chooses their role, reviews their rights/obligations, verifies the email (FEA
 * OTP when enabled), draws a signature and accepts the consent. Both roles must
 * sign for the document to be sealed.
 */
class SignatureController extends Controller
{
    public function __construct(
        private readonly SignatureService $signatures,
        private readonly PartyRightsObligations $rightsObligations,
    ) {}

    private function contractByToken(string $token): Contract
    {
        $contract = Contract::with(['parties', 'versions', 'signatures'])
            ->where('access_token', $token)
            ->firstOrFail();

        abort_unless(in_array($contract->status, ['lista_para_firma', 'en_firma', 'firmado'], true), 403, 'Este contrato no está en fase de firma.');
        abort_unless($contract->tokenIsValid(), 403, 'El enlace de firma ha caducado. Solicita uno nuevo.');

        return $contract;
    }

    public function show(Request $request, string $token): View
    {
        $contract = $this->contractByToken($token);

        app(ContractWorkflowService::class)->record(
            $contract,
            'signature_link_viewed',
            'system',
            detail: 'Enlace de firma visitado.'
        );

        $seller = $contract->seller();
        $buyer = $contract->buyer();
        $sellerSigned = $this->signatures->partyHasSigned($contract, 'vendedor');
        $buyerSigned = $this->signatures->partyHasSigned($contract, 'comprador');

        $requestedRole = $request->query('role') ?? $request->query('party');
        if ($requestedRole === 'seller') {
            $requestedRole = 'vendedor';
        }
        if ($requestedRole === 'buyer') {
            $requestedRole = 'comprador';
        }

        // Automatically determine active signer
        if ($requestedRole && in_array($requestedRole, ['vendedor', 'comprador'], true)) {
            $activeRole = $requestedRole;
        } elseif ($sellerSigned && ! $buyerSigned) {
            $activeRole = 'comprador';
        } elseif ($buyerSigned && ! $sellerSigned) {
            $activeRole = 'vendedor';
        } else {
            $activeRole = $contract->creator_role === 'vendedor' ? 'vendedor' : 'comprador';
        }

        $activeParty = $activeRole === 'vendedor' ? $seller : $buyer;
        $activeSigned = $activeRole === 'vendedor' ? $sellerSigned : $buyerSigned;

        $rights = [
            'vendedor' => $this->rightsObligations->for($contract, $seller),
            'comprador' => $this->rightsObligations->for($contract, $buyer),
        ];

        $otpEnabled = (bool) config('signing.otp_enabled', true);

        return view('public.sign', compact(
            'contract',
            'token',
            'rights',
            'otpEnabled',
            'seller',
            'buyer',
            'sellerSigned',
            'buyerSigned',
            'activeRole',
            'activeParty',
            'activeSigned'
        ));
    }

    /**
     * Public download of the signed/sealed PDF for the token holder.
     */
    public function download(string $token): Response
    {
        $contract = $this->contractByToken($token);

        if ($contract->final_pdf_path && Storage::disk('local')->exists($contract->final_pdf_path)) {
            return Storage::disk('local')->download(
                $contract->final_pdf_path,
                $contract->reference.'-firmado.pdf'
            );
        }

        return app(ContractPdfService::class)->download($contract);
    }

    public function requestOtp(Request $request, string $token): RedirectResponse
    {
        $contract = $this->contractByToken($token);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:vendedor,comprador'],
            'signer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $role = $data['role'];
        $party = $contract->parties()->where('role', $role)->first();
        $otherParty = $contract->parties()->where('role', '!=', $role)->first();

        if ($this->signatures->partyHasSigned($contract, $role)) {
            return back()->with('error', 'Esta parte ('.ucfirst($role).') ya ha firmado el contrato.');
        }

        // Authoritative email resolution
        if ($party && ! empty($party->email)) {
            $targetEmail = $party->email;
            if (! empty($data['signer_email']) && strtolower(trim($data['signer_email'])) !== strtolower(trim($party->email))) {
                return back()->with('error', 'El código de seguridad solo puede enviarse al correo oficial registrado para este rol ('.$party->email.').');
            }
        } else {
            $targetEmail = trim((string) ($data['signer_email'] ?? ''));
            if (empty($targetEmail)) {
                return back()->with('error', 'Debes introducir tu dirección de correo electrónico.');
            }
            if ($otherParty && ! empty($otherParty->email) && strtolower(trim($otherParty->email)) === strtolower($targetEmail)) {
                return back()->with('error', 'Este correo pertenece a la otra parte contratante. Debes usar tu propio correo personal o corporativo.');
            }
            $party?->update(['email' => $targetEmail]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $emailKey = strtolower($targetEmail);
        Cache::put('sign_otp:'.$token.':'.$role.':'.$emailKey, $code, now()->addMinutes(10));
        Cache::put('sign_otp:'.$token.':'.$emailKey, $code, now()->addMinutes(10));

        try {
            Mail::to($targetEmail)->send(new OtpMail($contract, $code));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar OTP de firma: '.$e->getMessage());
            return back()->with('error', 'No se pudo enviar el correo con el código de verificación. Por favor, inténtalo de nuevo en unos momentos.');
        }

        return back()->with('otp_sent', $targetEmail)->with('success', 'Código de verificación de 6 dígitos enviado a '.$targetEmail.'.');
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $contract = $this->contractByToken($token);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:vendedor,comprador'],
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_email' => ['required', 'email', 'max:255'],
            'signature_type' => ['required', 'string', 'in:fes-canvas,fes-click'],
            'signature_image' => ['nullable', 'string'],
            'consent' => ['required', 'accepted'],
        ]);

        $role = $data['role'];
        $party = $contract->parties()->where('role', $role)->first();
        $otherParty = $contract->parties()->where('role', '!=', $role)->first();

        if ($this->signatures->partyHasSigned($contract, $role)) {
            return back()->with('error', 'Esta parte ('.ucfirst($role).') ya ha firmado el contrato.');
        }

        // Validate party email ownership
        if ($party && ! empty($party->email) && strtolower(trim($party->email)) !== strtolower(trim($data['signer_email']))) {
            return back()->with('error', 'El correo de firma debe coincidir con el correo asignado a tu rol ('.$party->email.').')
                ->withInput($request->except('otp_code'));
        }

        if ($otherParty && ! empty($otherParty->email) && strtolower(trim($otherParty->email)) === strtolower(trim($data['signer_email']))) {
            return back()->with('error', 'No puedes firmar usando el correo de la otra parte contratante.')
                ->withInput($request->except('otp_code'));
        }

        if ((bool) config('signing.otp_enabled', true)) {
            $data = array_merge($data, $request->validate([
                'otp_code' => ['required', 'string', 'size:6'],
            ]));

            $emailKey = strtolower(trim($data['signer_email']));
            $stored = Cache::pull('sign_otp:'.$token.':'.$role.':'.$emailKey)
                   ?? Cache::pull('sign_otp:'.$token.':'.$emailKey);

            if ($stored === null || ! hash_equals((string) $stored, $data['otp_code'])) {
                return back()->with('error', 'El código de verificación es incorrecto o ha caducado. Solicítalo de nuevo.')
                    ->withInput($request->except('otp_code'));
            }
        }

        $consentText = 'Acepto firmar el contrato '.$contract->reference
            .' y declaro que he leído y acepto las condiciones del documento y el tratamiento de mis datos conforme a la normativa de protección de datos aplicable.';

        try {
            $this->signatures->sign(
                $contract,
                $data['role'],
                $data['signer_name'],
                $data['signer_email'],
                $data['signature_type'],
                $data['signature_image'] ?? null,
                $consentText,
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'otp_verified' => (bool) config('signing.otp_enabled', true),
                ]
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $remaining = ['vendedor' => 'vendedor', 'comprador' => 'comprador'];
        unset($remaining[$data['role']]);
        $nextRole = reset($remaining);

        $message = 'Firma registrada correctamente. ';
        if (! $this->signatures->allPartiesSigned($contract->fresh())) {
            $message .= 'Falta la firma del '.$nextRole.'.';
        } else {
            $message .= 'El contrato está firmado por ambas partes y se ha sellado con la hoja de evidencias.';
        }

        return redirect()->route('sign.show', $token)->with('success', $message);
    }

    public function downloadDocument(string $token, \App\Models\ContractDocument $document): \Symfony\Component\HttpFoundation\Response
    {
        $contract = $this->contractByToken($token);
        abort_unless($document->contract_id === $contract->id, 404);

        $diskName = config('filesystems.documents_disk', 'local');
        $disk = \Illuminate\Support\Facades\Storage::disk($diskName);
        if (! $disk->exists($document->path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($document->path)) {
            $disk = \Illuminate\Support\Facades\Storage::disk('local');
        }

        abort_unless($disk->exists($document->path), 404, 'El archivo solicitado no se encuentra en el almacenamiento.');

        return $disk->download($document->path, $document->filename);
    }
}
