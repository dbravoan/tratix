<?php

namespace App\Http\Controllers;

use App\Mail\ProposalCreatedMail;
use App\Mail\ReviewAcceptedMail;
use App\Models\AuditEvent;
use App\Models\Contract;
use App\Services\ClauseBuilder;
use App\Services\ContractPdfService;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\NegotiationService;
use App\Services\PartyRightsObligations;
use App\Services\TransactionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public review endpoint: the counterparty opens a link, sees the draft and
 * either accepts it or proposes changes (track-changes). No account required.
 */
class ReviewController extends Controller
{
    public function __construct(
        private readonly NegotiationService $negotiation,
        private readonly ContractWorkflowService $workflow,
        private readonly PartyRightsObligations $rightsObligations,
        private readonly ContractService $contractService,
    ) {}

    private function contractByToken(string $token): Contract
    {
        $contract = Contract::with(['parties', 'proposals', 'versions', 'comments'])
            ->where('access_token', $token)
            ->firstOrFail();

        abort_unless(in_array($contract->status, ['borrador', 'en_revision'], true), 403, 'Este contrato no está en fase de revisión.');
        abort_unless($contract->tokenIsValid(), 403, 'El enlace de revisión ha caducado. Solicita uno nuevo.');

        return $contract;
    }

    public function show(Request $request, string $token): View
    {
        $contract = $this->contractByToken($token);

        $seller = $contract->seller();
        $buyer = $contract->buyer();
        $counterparty = $contract->counterparty();
        $activeRole = $request->query('role') ?? $request->query('party') ?? $counterparty?->role ?? 'comprador';
        if ($activeRole === 'seller') {
            $activeRole = 'vendedor';
        }
        if ($activeRole === 'buyer') {
            $activeRole = 'comprador';
        }
        $activeParty = $activeRole === 'vendedor' ? $seller : $buyer;

        $rights = [
            'vendedor' => $this->rightsObligations->for($contract, $seller),
            'comprador' => $this->rightsObligations->for($contract, $buyer),
        ];

        return view('public.review', compact('contract', 'token', 'rights', 'seller', 'buyer', 'counterparty', 'activeRole', 'activeParty'));
    }

    /**
     * Public download of the draft PDF for the token holder.
     */
    public function download(string $token): Response
    {
        $contract = $this->contractByToken($token);

        return app(ContractPdfService::class)->download($contract);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $contract = $this->contractByToken($token);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:vendedor,comprador'],
            'acceptor_name' => ['required', 'string', 'max:255'],
        ]);

        $this->workflow->record(
            $contract,
            'review_accepted',
            $data['role'],
            null,
            'Revisión aceptada por '.$data['acceptor_name'].' ('.$data['role'].').'
        );

        $contract->loadMissing('user');
        if ($contract->user?->email && ($contract->user->notify_proposals ?? true)) {
            try {
                Mail::to($contract->user->email)->queue(
                    new ReviewAcceptedMail($contract, $data['acceptor_name'], $data['role'], route('contracts.show', $contract))
                );
            } catch (\Throwable $e) {
                Log::warning('review.accept_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('review.show', $token)
            ->with('success', 'Gracias, tu aceptación del borrador ha quedado registrada.');
    }

    public function propose(Request $request, string $token): RedirectResponse
    {
        $contract = $this->contractByToken($token);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:vendedor,comprador'],
            'clause_key' => ['required', 'string'],
            'proposed_text' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $proposal = $this->negotiation->propose(
                $contract,
                $data['clause_key'],
                $data['proposed_text'],
                $data['role'],
                $data['reason'] ?? null,
                $data['role']
            );

            $contract->loadMissing('user');
            if ($contract->user?->email && ($contract->user->notify_proposals ?? true)) {
                try {
                    Mail::to($contract->user->email)->queue(
                        new ProposalCreatedMail($contract, $proposal, route('contracts.show', $contract))
                    );
                } catch (\Throwable $e) {
                    Log::warning('proposal.notify_failed', ['error' => $e->getMessage()]);
                }
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('review.show', $token)
            ->with('success', 'Tu propuesta de cambio se ha enviado al creador del contrato.');
    }

    public function updateParty(Request $request, string $token): RedirectResponse
    {
        $contract = $this->contractByToken($token);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:vendedor,comprador'],
            'party_type' => ['required', 'string', 'in:particular,autonomo,sociedad'],
            'full_name' => ['required_if:party_type,particular', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:party_type,autonomo', 'required_if:party_type,sociedad', 'nullable', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:32'],
            'tax_id_country' => ['required', 'string', 'size:2'],
            'country' => ['required', 'string', 'size:2'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:16'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'activity' => ['nullable', 'string', 'max:255'],
            'id_card_front_token' => ['nullable', 'string', 'max:100'],
            'id_card_back_token' => ['nullable', 'string', 'max:100'],
            'id_card_token' => ['nullable', 'string', 'max:100'],
        ]);

        $role = $data['role'];
        $party = $contract->parties()->where('role', $role)->first();

        if ($party) {
            $party->update($data);
        } else {
            $contract->parties()->create($data);
        }

        // Attach documents if scanned tokens exist
        if (! empty($data['id_card_front_token'])) {
            $this->contractService->attachScannedIdCard($contract, $data['id_card_front_token'], "{$role}_anverso");
        }
        if (! empty($data['id_card_back_token'])) {
            $this->contractService->attachScannedIdCard($contract, $data['id_card_back_token'], "{$role}_reverso");
        }

        // Re-resolve transaction regime & refresh clauses
        $contract->load('parties');
        $seller = $contract->seller();
        $buyer = $contract->buyer();
        if ($seller && $buyer) {
            $resolution = app(TransactionResolver::class)->resolve($seller, $buyer);
            $clauses = app(ClauseBuilder::class)->build($contract, $seller, $buyer, $resolution);
            $contract->update([
                'transaction_type' => $resolution['transaction_type'],
                'jurisdiction' => $resolution['jurisdiction'],
                'clauses' => $clauses,
            ]);
        }

        $displayName = $data['full_name'] ?? $data['company_name'] ?? 'Contraparte';

        AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => null,
            'event' => 'party_updated',
            'actor' => "{$displayName} ({$role})",
            'detail' => "Datos fiscales de {$role} actualizados por la contraparte en la revisión.",
            'happened_at' => now(),
        ]);

        return redirect()->route('review.show', ['token' => $token, 'role' => $role])
            ->with('success', 'Tus datos legales se han guardado y el borrador se ha actualizado correctamente.');
    }
}
