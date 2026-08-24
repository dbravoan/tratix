<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Seals a fully signed contract:
 *  1. Generates the final PDF (document + signatures + evidence certificate).
 *  2. Computes the SHA-256 of the final PDF bytes (integrity anchor).
 *  3. Builds an evidence payload and requests an RFC 3161 timestamp (optional,
 *     graceful fallback to server time).
 *  4. Stores the evidence sidecar files and updates the contract record.
 */
class SealingService
{
    public function __construct(private readonly TsaService $tsa) {}

    /**
     * @return Contract the sealed contract
     */
    public function seal(Contract $contract): Contract
    {
        $contract->load(['parties', 'signatures', 'versions']);
        $version = $contract->latestVersion();

        $contract = $this->ensureStatusFirmado($contract);

        // 1. Final PDF
        $pdfHash = $this->generateFinalPdf($contract, $version);

        // 2. Evidence payload + TSA
        $tsaResult = $this->timestampEvidence($contract, $version, $pdfHash);

        // 3. Persist records
        $contract->update([
            'final_pdf_path' => $this->finalPdfRelativePath($contract),
            'final_hash' => $pdfHash,
            'signed_version' => $version?->version,
            'sealed_at' => now(),
        ]);

        $sealing = app(ContractWorkflowService::class);
        $sealing->record($contract, 'sealed', 'system', null, 'Documento sellado con hash SHA-256'.($tsaResult ? ' y sello TSA.' : '.'));

        return $contract->fresh();
    }

    private function ensureStatusFirmado(Contract $contract): Contract
    {
        if ($contract->status !== 'firmado') {
            $contract->update(['status' => 'firmado']);
        }

        return $contract->fresh();
    }

    /**
     * Generates and stores the final PDF. Returns the SHA-256 of the stored
     * bytes, so verification always recomputes the exact file we keep.
     */
    private function generateFinalPdf(Contract $contract, $version): string
    {
        $signatures = $contract->signatures()->orderBy('signed_at')->get();

        $base = [
            'contract' => $contract,
            'version' => $version,
            'signatures' => $signatures,
            'sealedAt' => now(),
            'tsa' => null,
        ];

        // First pass: render to compute the digest (hash shown inside the doc).
        $placeholder = Pdf::loadView('pdfs.contract_final', [...$base, 'pdfHash' => '']);
        $digest = hash('sha256', $placeholder->output());

        // Second pass: the stored file embeds the digest it will be sealed under.
        $finalBytes = Pdf::loadView('pdfs.contract_final', [...$base, 'pdfHash' => $digest])->output();
        $storedHash = hash('sha256', $finalBytes);

        $relative = $this->finalPdfRelativePath($contract);
        Storage::disk('local')->put($relative, $finalBytes);

        return $storedHash;
    }

    private function timestampEvidence(Contract $contract, $version, string $pdfHash): ?array
    {
        $payloadText = $this->buildEvidencePayload($contract, $version, $pdfHash);
        $payloadFile = tempnam(sys_get_temp_dir(), 'evid');
        file_put_contents($payloadFile, $payloadText);

        $tsaResult = $this->tsa->timestamp($payloadFile);

        $relative = $this->evidenceRelativePath($contract, 'payload');
        Storage::disk('local')->put($relative, $payloadText);

        if ($tsaResult) {
            Storage::disk('local')->put($this->evidenceRelativePath($contract, 'tsr'), base64_decode($tsaResult['tsr_base64']));
            $tsaResult['url'] = $this->tsa->url();
        }

        @unlink($payloadFile);

        return $tsaResult;
    }

    private function buildEvidencePayload(Contract $contract, $version, string $pdfHash): string
    {
        $signers = collect($contract->signatures)
            ->map(fn ($s) => sprintf(
                '%s|%s|%s|%s|%s',
                $s->party_role,
                $s->signer_name,
                $s->signer_email,
                $s->signed_at->toIso8601String(),
                $s->ip ?? ''
            ))
            ->implode("\n");

        return implode("\n", [
            'REFERENCE: '.$contract->reference,
            'VERSION: '.($version?->version ?? '—'),
            'CONTENT_HASH_SHA256: '.($version?->hash ?? '—'),
            'FINAL_PDF_SHA256: '.$pdfHash,
            'SEALED_AT_UTC: '.now()->toIso8601String(),
            'SIGNERS:',
            $signers ?: '—',
            'SIGNING_REGIME: '.$contract->transaction_type,
            'JURISDICTION: '.$contract->jurisdiction,
        ]);
    }

    private function finalPdfRelativePath(Contract $contract): string
    {
        return 'contracts/'.$contract->reference.'/final-v'.($contract->signed_version ?? $contract->versions()->max('version') ?? 1).'.pdf';
    }

    private function evidenceRelativePath(Contract $contract, string $type): string
    {
        return 'contracts/'.$contract->reference.'/evidence-'.$type.'.txt';
    }
}
