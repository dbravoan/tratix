<?php

namespace App\Http\Controllers;

use App\Mail\ContractCancelledMail;
use App\Mail\ReviewInviteMail;
use App\Mail\SignatureInviteMail;
use App\Models\Contract;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContractWorkflowController extends Controller
{
    public function __construct(
        private readonly ContractWorkflowService $workflow,
        private readonly SignatureService $signature,
    ) {}

    public function sendReview(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'invited_email' => ['required', 'email', 'max:255'],
            'review_deadline' => ['nullable', 'date'],
        ]);

        $contract->update([
            'invited_email' => $data['invited_email'],
            'review_deadline' => $data['review_deadline'] ?? null,
        ]);

        $contract = $this->signature->ensureToken($contract);

        $contract = $this->workflow->transition(
            $contract,
            'en_revision',
            'creator',
            auth()->user(),
            'Invitación de revisión enviada a '.$data['invited_email'].'.'
        );

        $role = $this->counterpartyRole($contract);

        Mail::to($data['invited_email'])->queue(
            new ReviewInviteMail($contract, route('review.show', $contract->access_token), $role)
        );

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contrato enviado a revisión. Comparte el enlace de revisión con la otra parte.');
    }

    public function acceptFinal(Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $contract = $this->workflow->transition($contract, 'lista_para_firma', 'creator', auth()->user());

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Versión final acordada y congelada (v'.$contract->latestVersion()->version.'). Ahora puedes enviarlo a firmar.');
    }

    public function sendSignature(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate(['signer_email' => ['required', 'email', 'max:255']]);

        $contract = $this->workflow->transition(
            $contract,
            'en_firma',
            'creator',
            auth()->user(),
            'Enlace de firma compartido con '.$data['signer_email'].'.'
        );

        $role = $this->counterpartyRole($contract);
        $link = route('sign.show', ['token' => $contract->access_token, 'role' => $role]);

        Mail::to($data['signer_email'])->queue(
            new SignatureInviteMail($contract, $link)
        );

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contrato en fase de firma. Enlace de firma: '.$link);
    }

    public function cancel(Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $contract = $this->workflow->transition($contract, 'cancelado', 'creator', auth()->user());

        $targetEmail = $contract->counterparty()?->email ?? $contract->invited_email;
        if ($targetEmail) {
            try {
                Mail::to($targetEmail)->queue(
                    new ContractCancelledMail($contract)
                );
            } catch (\Throwable $e) {
                Log::warning('contract.cancel_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contrato cancelado. Si hay cambios, crea una versión nueva.');
    }

    /**
     * The role the invited counterparty must pick (the one not owned by the creator).
     */
    private function counterpartyRole(Contract $contract): string
    {
        return $contract->counterparty()?->role ?? 'comprador';
    }
}
