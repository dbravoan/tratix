<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Services\ContractExportService;
use App\Services\ContractLegalValidator;
use App\Services\ContractPdfService;
use App\Services\ContractService;
use App\Services\CountryLegalConfig;
use App\Services\CreditService;
use App\Services\DocumentGuidanceService;
use App\Services\EuVatValidator;
use App\Services\IdentityCardParserService;
use App\Services\LatinAmericanTaxIdValidator;
use App\Services\SignatureService;
use App\Services\SpanishTaxIdValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        private readonly ContractService $contractService,
        private readonly ContractLegalValidator $legalValidator,
        private readonly ContractPdfService $pdfService,
        private readonly SignatureService $signatureService,
        private readonly DocumentGuidanceService $guidance,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Contract::with(['parties', 'signatures'])->where('user_id', $user->id);

        if ($status = $request->query('status')) {
            if ($status === 'en_firma') {
                $query->whereIn('status', ['lista_para_firma', 'en_firma']);
            } else {
                $query->where('status', $status);
            }
        }
        if ($type = $request->query('type')) {
            $query->where('contract_type', $type);
        }
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('object_description', 'like', "%{$search}%");
            });
        }

        $contracts = $query->latest()->paginate(15)->withQueryString();

        $creditService = app(CreditService::class);
        $usedThisMonth = $creditService->usedThisMonth($user);
        $remaining = $creditService->remaining($user);

        $counts = [
            'all' => Contract::where('user_id', $user->id)->count(),
            'borrador' => Contract::where('user_id', $user->id)->where('status', 'borrador')->count(),
            'en_revision' => Contract::where('user_id', $user->id)->where('status', 'en_revision')->count(),
            'en_firma' => Contract::where('user_id', $user->id)->whereIn('status', ['lista_para_firma', 'en_firma'])->count(),
            'firmado' => Contract::where('user_id', $user->id)->where('status', 'firmado')->count(),
            'cancelado' => Contract::where('user_id', $user->id)->where('status', 'cancelado')->count(),
        ];

        return view('contracts.index', [
            'user' => $user,
            'contracts' => $contracts,
            'counts' => $counts,
            'usedThisMonth' => $usedThisMonth,
            'remaining' => $remaining,
            'filters' => [
                'status' => $status ?? '',
                'type' => $type ?? '',
                'search' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        $remaining = app(CreditService::class)->remaining(auth()->user());

        return view('contracts.create', compact('remaining'));
    }

    public function store(StoreContractRequest $request): RedirectResponse
    {
        if (! app(CreditService::class)->canCreate(auth()->user())) {
            return redirect()->route('billing.pricing')
                ->with('error', 'Has agotado tus contratos gratuitos de este mes. Actualiza tu plan para continuar.');
        }

        $data = $request->safe()->except(['seller', 'buyer']);
        $sellerData = $request->safe()->input('seller');
        $buyerData = $request->safe()->input('buyer');

        $sellerData['role'] = 'vendedor';
        $buyerData['role'] = 'comprador';
        $sellerData['registered_vat'] = $request->boolean('seller.registered_vat');
        $buyerData['registered_vat'] = $request->boolean('buyer.registered_vat');
        $sellerData['acting_in_own_name'] = $request->boolean('seller.acting_in_own_name');
        $buyerData['acting_in_own_name'] = $request->boolean('buyer.acting_in_own_name');

        $contract = $this->contractService->create(
            $data,
            $sellerData,
            $buyerData,
            auth()->id()
        );

        app(CreditService::class)->consumeIfApplicable(auth()->user());

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contrato creado. Revisa el documento y los trámites pendientes antes de firmar.');
    }

    public function edit(Contract $contract): View|RedirectResponse
    {
        $this->authorize('update', $contract);

        if (! in_array($contract->status, ['borrador', 'en_revision'], true)) {
            return redirect()->route('contracts.show', $contract)
                ->with('error', 'El contrato ya no se puede editar porque está en fase de firma o firmado.');
        }

        $contract->load('parties');
        $seller = $contract->seller();
        $buyer = $contract->buyer();

        return view('contracts.edit', compact('contract', 'seller', 'buyer'));
    }

    public function update(UpdateContractRequest $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->safe()->except(['seller', 'buyer']);
        $sellerData = $request->safe()->input('seller');
        $buyerData = $request->safe()->input('buyer');

        $sellerData['role'] = 'vendedor';
        $buyerData['role'] = 'comprador';
        $sellerData['registered_vat'] = $request->boolean('seller.registered_vat');
        $buyerData['registered_vat'] = $request->boolean('buyer.registered_vat');
        $sellerData['acting_in_own_name'] = $request->boolean('seller.acting_in_own_name');
        $buyerData['acting_in_own_name'] = $request->boolean('buyer.acting_in_own_name');

        try {
            $this->contractService->update(
                $contract,
                $data,
                $sellerData,
                $buyerData,
                auth()->id()
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contrato actualizado correctamente con los nuevos datos.');
    }

    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load(['parties', 'versions', 'proposals', 'signatures', 'documents', 'auditEvents', 'comments']);
        $issues = $this->legalValidator->validate($contract);
        $checklist = $this->guidance->checklist($contract);
        $completeness = $this->guidance->completeness($contract);

        return view('contracts.show', compact('contract', 'issues', 'checklist', 'completeness'));
    }

    public function preview(Contract $contract): Response
    {
        $this->authorize('view', $contract);

        return $this->pdfService->render($contract)->stream();
    }

    public function download(Contract $contract): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $contract);

        if ($contract->final_pdf_path && Storage::disk('local')->exists($contract->final_pdf_path)) {
            return Storage::disk('local')->download(
                $contract->final_pdf_path,
                $contract->reference.'-firmado.pdf'
            );
        }

        return $this->pdfService->download($contract);
    }

    public function evidence(Contract $contract): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $contract);

        $evidencePath = 'contracts/'.$contract->reference.'/evidence-payload.txt';
        if (Storage::disk('local')->exists($evidencePath)) {
            return Storage::disk('local')->download(
                $evidencePath,
                $contract->reference.'-evidencia.txt'
            );
        }

        if ($contract->final_pdf_path && Storage::disk('local')->exists($contract->final_pdf_path)) {
            return Storage::disk('local')->download(
                $contract->final_pdf_path,
                $contract->reference.'-final.pdf'
            );
        }

        return redirect()->route('contracts.show', $contract)
            ->with('error', 'La hoja de evidencias aún no está disponible.');
    }

    public function verify(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('view', $contract);

        $result = $this->contractService->verifyIntegrity($contract);

        $flash = $result['valid']
            ? 'Integridad verificada: el hash del PDF coincide con el sellado.'
            : 'ALERTA: el hash no coincide. El documento pudo ser modificado.';

        return redirect()
            ->route('contracts.show', $contract)
            ->with($result['valid'] ? 'success' : 'error', $flash.' ('.$result['detail'].')');
    }

    public function signingLink(Contract $contract): RedirectResponse
    {
        $this->authorize('view', $contract);

        return redirect()->away($this->signatureService->signingLink($contract));
    }

    public function reviewLink(Contract $contract): RedirectResponse
    {
        $this->authorize('view', $contract);

        $token = $this->signatureService->ensureToken($contract)->access_token;

        return redirect()->away(route('review.show', $token));
    }

    public function destroy(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('delete', $contract);

        $contract->delete();

        return redirect()->route('contracts.index')->with('success', 'Contrato eliminado.');
    }

    public function exportAll(): \Symfony\Component\HttpFoundation\Response
    {
        if (! in_array(auth()->user()->plan, ['pro', 'business'], true)) {
            return redirect()->route('billing.pricing')
                ->with('error', 'La exportación de todos tus contratos está disponible en los planes Pro y Business.');
        }

        $path = app(ContractExportService::class)->export(auth()->user());

        if (! $path) {
            return redirect()->route('dashboard')->with('info', 'No tienes contratos que exportar.');
        }

        return response()->download($path)->deleteFileAfterSend();
    }

    public function scanIdCard(Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,webp'],
            'side' => ['nullable', 'string', 'in:front,back,auto'],
            'ocr_text' => ['nullable', 'string', 'max:50000'],
        ]);

        $parser = app(IdentityCardParserService::class);
        $result = $parser->parse(
            $request->file('document'),
            $request->input('ocr_text'),
            $request->input('side', 'auto')
        );

        return response()->json($result);
    }

    public function checkTaxId(Request $request): array
    {
        $country = strtoupper((string) $request->input('country', 'ES'));
        $taxId = (string) $request->input('tax_id');

        if ($country === 'ES') {
            $valid = app(SpanishTaxIdValidator::class)->isValid($taxId);

            return ['valid' => $valid, 'type' => $this->taxIdKind($taxId)];
        }

        if (app(CountryLegalConfig::class)->isSupported($country)) {
            $valid = app(LatinAmericanTaxIdValidator::class)->isValid($country, $taxId);

            return [
                'valid' => $valid,
                'type' => 'tax-'.strtolower($country),
                'vies_valid' => false,
                'vies_checked' => false,
            ];
        }

        $vat = app(EuVatValidator::class);
        $valid = $vat->hasValidFormat($country, $taxId);
        $vies = $vat->validate($country, $taxId);

        return [
            'valid' => $valid,
            'type' => 'vat-'.strtolower($country),
            'vies_valid' => $vies['valid'],
            'vies_checked' => $vies['checked_via_vies'],
        ];
    }

    private function taxIdKind(string $taxId): string
    {
        $taxId = strtoupper(trim($taxId));

        return match (true) {
            strlen($taxId) === 9 && preg_match('/^[XYZ]\d{7}[A-Z]$/', $taxId) === 1 => 'nie',
            strlen($taxId) === 9 && preg_match('/^\d{8}[A-Z]$/', $taxId) === 1 => 'nif',
            strlen($taxId) === 9 && preg_match('/^[A-Z]\d{7}[0-9A-J]$/', $taxId) === 1 => 'cif',
            default => 'desconocido',
        };
    }
}
