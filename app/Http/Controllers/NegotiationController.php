<?php

namespace App\Http\Controllers;

use App\Mail\ProposalCreatedMail;
use App\Mail\ProposalResolvedMail;
use App\Models\ClauseProposal;
use App\Models\Contract;
use App\Services\NegotiationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NegotiationController extends Controller
{
    public function __construct(private readonly NegotiationService $negotiation) {}

    public function store(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'clause_key' => ['required', 'string'],
            'proposed_text' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $proposal = $this->negotiation->propose(
                $contract,
                $data['clause_key'],
                $data['proposed_text'],
                'creator',
                $data['reason'] ?? null,
                'creator'
            );

            $counterparty = $contract->counterparty();
            $targetEmail = $counterparty?->email ?? $contract->invited_email;
            if ($targetEmail && $contract->access_token) {
                try {
                    Mail::to($targetEmail)->queue(
                        new ProposalCreatedMail($contract, $proposal, route('review.show', $contract->access_token))
                    );
                } catch (\Throwable $e) {
                    Log::warning('proposal.notify_counterparty_failed', ['error' => $e->getMessage()]);
                }
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Propuesta de cambio registrada. Debe ser aceptada antes de cerrar la versión.');
    }

    public function approve(Contract $contract, ClauseProposal $proposal): RedirectResponse
    {
        $this->authorize('update', $contract);

        try {
            $this->negotiation->approve($contract, $proposal, 'creator');

            $counterparty = $contract->counterparty();
            $targetEmail = $counterparty?->email ?? $contract->invited_email;
            if ($targetEmail && $contract->access_token) {
                try {
                    Mail::to($targetEmail)->queue(
                        new ProposalResolvedMail($contract, $proposal, route('review.show', $contract->access_token))
                    );
                } catch (\Throwable $e) {
                    Log::warning('proposal.resolved_notify_failed', ['error' => $e->getMessage()]);
                }
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cambio aprobado y aplicado al documento.');
    }

    public function reject(Contract $contract, ClauseProposal $proposal): RedirectResponse
    {
        $this->authorize('update', $contract);

        try {
            $this->negotiation->reject($contract, $proposal, 'creator');

            $counterparty = $contract->counterparty();
            $targetEmail = $counterparty?->email ?? $contract->invited_email;
            if ($targetEmail && $contract->access_token) {
                try {
                    Mail::to($targetEmail)->queue(
                        new ProposalResolvedMail($contract, $proposal, route('review.show', $contract->access_token))
                    );
                } catch (\Throwable $e) {
                    Log::warning('proposal.resolved_notify_failed', ['error' => $e->getMessage()]);
                }
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cambio rechazado.');
    }
}
