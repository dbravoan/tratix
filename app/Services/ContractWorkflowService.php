<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Contract;
use App\Models\ContractVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Contract state machine. Every transition is validated, persists the new
 * state and writes an immutable audit trail entry.
 *
 * States: borrador → en_revision → lista_para_firma → en_firma → firmado
 *         any state → cancelado
 *
 * A version is frozen when the contract becomes ready to sign. After that,
 * any material change requires cancelling and creating a new version.
 */
class ContractWorkflowService
{
    private const TRANSITIONS = [
        'borrador' => ['en_revision', 'lista_para_firma', 'cancelado'],
        'en_revision' => ['borrador', 'lista_para_firma', 'cancelado'],
        'lista_para_firma' => ['en_firma', 'cancelado'],
        'en_firma' => ['firmado', 'cancelado'],
        'firmado' => [],
        'cancelado' => [],
    ];

    private const EVENT_FOR_TRANSITION = [
        'borrador' => ['en_revision' => 'sent_for_review', 'lista_para_firma' => 'version_frozen', 'cancelado' => 'cancelled'],
        'en_revision' => ['borrador' => 'sent_for_review', 'lista_para_firma' => 'version_frozen', 'cancelado' => 'cancelled'],
        'lista_para_firma' => ['en_firma' => 'sent_for_signature', 'cancelado' => 'cancelled'],
        'en_firma' => ['firmado' => 'signed', 'cancelado' => 'cancelled'],
        'firmado' => [],
        'cancelado' => [],
    ];

    public function canTransition(Contract $contract, string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$contract->status] ?? [], true);
    }

    public function allowedNextStates(Contract $contract): array
    {
        return self::TRANSITIONS[$contract->status] ?? [];
    }

    /**
     * @throws \DomainException when the transition is not allowed
     */
    public function transition(
        Contract $contract,
        string $newStatus,
        string $actor,
        ?User $user = null,
        ?string $detail = null,
    ): Contract {
        if (! $this->canTransition($contract, $newStatus)) {
            throw new \DomainException(
                "No se puede pasar de estado «{$contract->status}» a «{$newStatus}»."
            );
        }

        $oldStatus = $contract->status;
        $event = self::EVENT_FOR_TRANSITION[$oldStatus][$newStatus];

        $contract->update(['status' => $newStatus]);

        if ($newStatus === 'lista_para_firma') {
            $this->freezeVersion($contract, $user);
        }

        $this->record($contract, $event, $actor, $user, $detail ?: "Estado: {$oldStatus} → {$newStatus}");

        return $contract->fresh();
    }

    /**
     * Freezes the current clause set into an immutable version with a content
     * hash. Called when a contract becomes ready to sign.
     */
    public function freezeVersion(Contract $contract, ?User $user = null): ContractVersion
    {
        $clauses = $contract->clauses ?? [];
        $clauses = $this->normalize($clauses);

        $versionNumber = ($contract->versions()->max('version') ?? 0) + 1;

        $hash = hash('sha256', $this->canonical($clauses));

        return DB::transaction(function () use ($contract, $versionNumber, $clauses, $hash) {
            return ContractVersion::create([
                'contract_id' => $contract->id,
                'version' => $versionNumber,
                'clauses' => $clauses,
                'hash' => $hash,
                'frozen_at' => now(),
            ]);
        });
    }

    public function record(Contract $contract, string $event, string $actor, ?User $user = null, ?string $detail = null): AuditEvent
    {
        return AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => $user?->id,
            'event' => $event,
            'actor' => $actor,
            'detail' => $detail,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500) ?: null,
            'happened_at' => now(),
        ]);
    }

    private function normalize(array $clauses): array
    {
        $normalized = [];
        foreach ($clauses as $index => $clause) {
            $key = is_string($index) ? $index : (string) $clause['key'] ?? (string) $index;
            $normalized[] = [
                'key' => $key,
                'title' => $clause['title'] ?? 'Cláusula',
                'body' => $clause['body'] ?? $clause['text'] ?? '',
            ];
        }

        return $normalized;
    }

    /**
     * Deterministic canonical serialisation so equal content always hashes
     * identically regardless of key ordering.
     */
    private function canonical(array $clauses): string
    {
        ksort($clauses);

        return json_encode($clauses, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
