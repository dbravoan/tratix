<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\View\View;

/**
 * Public document-integrity verification: anyone with the reference can confirm
 * a sealed contract has not been altered. Strong trust signal + shareable.
 */
class PublicVerificationController extends Controller
{
    public function __construct(private readonly ContractService $contractService) {}

    public function show(string $reference): View
    {
        $contract = Contract::where('reference', strtoupper($reference))->first();

        if (! $contract || ! $contract->final_hash || ! $contract->final_pdf_path) {
            return view('verify', ['found' => false]);
        }

        $result = $this->contractService->verifyIntegrity($contract);

        return view('verify', [
            'found' => true,
            'contract' => $contract,
            'valid' => $result['valid'],
            'hash' => $contract->final_hash,
        ]);
    }
}
