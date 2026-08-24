<?php

namespace App\Services;

use App\Models\ClauseProposal;
use App\Models\Contract;

/**
 * Negotiation of individual clauses before the document is frozen for signing.
 *
 * The creator or the counterparty (via the review link) proposes a change to a
 * specific clause. The change is recorded as a "track-changes" proposal and,
 * once approved, replaces the clause text in the working draft. The final
 * agreed text is frozen into an immutable version when the contract becomes
 * ready to sign.
 */
class NegotiationService
{
    public function __construct(private readonly ContractWorkflowService $workflow) {}

    /**
     * @throws \DomainException if the contract is not in a negotiable state
     */
    public function propose(
        Contract $contract,
        string $clauseKey,
        string $proposedText,
        string $proposedBy,
        ?string $reason = null,
        ?string $actor = null,
    ): ClauseProposal {
        if (! in_array($contract->status, ['borrador', 'en_revision'], true)) {
            throw new \DomainException('Solo se pueden proponer cambios en borrador o en revisión.');
        }

        $clause = $this->findClause($contract, $clauseKey);

        $proposal = ClauseProposal::create([
            'contract_id' => $contract->id,
            'contract_version_id' => $contract->latestVersion()?->id,
            'clause_key' => $clauseKey,
            'clause_title' => $clause['title'] ?? null,
            'original_text' => $clause['body'] ?? '',
            'proposed_text' => $proposedText,
            'proposed_by' => $proposedBy,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        $this->workflow->record(
            $contract,
            'proposal_created',
            $actor ?? $proposedBy,
            null,
            "Cláusula «{$clauseKey}» propuesta por {$proposedBy}."
        );

        return $proposal->fresh();
    }

    public function approve(Contract $contract, ClauseProposal $proposal, string $actor): ClauseProposal
    {
        $this->assertEditable($contract, $proposal);

        $proposal->update(['status' => 'approved']);
        $this->applyProposedText($contract, $proposal);

        $this->workflow->record(
            $contract,
            'proposal_approved',
            $actor,
            null,
            "Cambio aprobado en cláusula «{$proposal->clause_key}»."
        );

        return $proposal->fresh();
    }

    public function reject(Contract $contract, ClauseProposal $proposal, string $actor): ClauseProposal
    {
        $this->assertEditable($contract, $proposal);

        $proposal->update(['status' => 'rejected']);

        $this->workflow->record(
            $contract,
            'proposal_rejected',
            $actor,
            null,
            "Cambio rechazado en cláusula «{$proposal->clause_key}»."
        );

        return $proposal->fresh();
    }

    public function pendingCount(Contract $contract): int
    {
        return $contract->proposals()->where('status', 'pending')->count();
    }

    private function assertEditable(Contract $contract, ClauseProposal $proposal): void
    {
        if ($proposal->contract_id !== $contract->id) {
            throw new \DomainException('La propuesta no pertenece a este contrato.');
        }
        if ($proposal->status !== 'pending') {
            throw new \DomainException('La propuesta ya ha sido resuelta.');
        }
        if (! in_array($contract->status, ['borrador', 'en_revision'], true)) {
            throw new \DomainException('El contrato ya no admite modificaciones.');
        }
    }

    /**
     * @return array{key: string, title: string, body: string}
     */
    private function findClause(Contract $contract, string $clauseKey): array
    {
        foreach (($contract->clauses ?? []) as $clause) {
            $key = $clause['key'] ?? $clause['title'] ?? null;
            if ((string) $key === $clauseKey) {
                return $clause;
            }
        }

        throw new \DomainException("No existe la cláusula «{$clauseKey}».");
    }

    private function applyProposedText(Contract $contract, ClauseProposal $proposal): void
    {
        $clauses = $contract->clauses ?? [];

        foreach ($clauses as &$clause) {
            $key = $clause['key'] ?? $clause['title'] ?? null;
            if ((string) $key === $proposal->clause_key) {
                $clause['body'] = $proposal->proposed_text;
            }
        }
        unset($clause);

        $contract->update(['clauses' => $clauses]);
    }
}
