<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ContractPdfService
{
    public function render(Contract $contract): \Barryvdh\DomPDF\PDF
    {
        $contract->load('parties');

        $whiteLabel = $contract->user?->plan === 'business';

        return Pdf::loadView('pdfs.contract', [
            'contract' => $contract,
            'white_label' => $whiteLabel,
        ]);
    }

    public function download(Contract $contract): Response
    {
        $pdf = $this->render($contract);
        $filename = sprintf('%s-%s.pdf', $contract->reference, now()->format('Ymd'));

        return $pdf->download($filename);
    }
}
