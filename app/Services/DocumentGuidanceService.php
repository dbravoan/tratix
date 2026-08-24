<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\DocumentRequirement;
use Illuminate\Support\Collection;

/**
 * Returns the ordered checklist of required documents for a contract, each
 * item annotated with its current upload state. Requirements are selected by
 * (contract_type, transaction_type, jurisdiction) with generic fallbacks.
 */
class DocumentGuidanceService
{
    /**
     * @return Collection<int, array{
     *   requirement: DocumentRequirement, uploaded: bool, validated: bool
     * }>
     */
    public function checklist(Contract $contract): Collection
    {
        $country = $contract->applicableLaw();

        $base = DocumentRequirement::query()
            ->where('contract_type', $contract->contract_type)
            ->where(function ($q) use ($contract) {
                $q->where('transaction_type', $contract->transaction_type)
                    ->orWhereNull('transaction_type');
            })
            ->where(function ($q) use ($contract) {
                $q->where('jurisdiction', $contract->jurisdiction)
                    ->orWhereNull('jurisdiction');
            });

        // Country-specific list wins; generic rows are the fallback.
        $requirements = (clone $base)
            ->where('country', $country)
            ->orderBy('order')
            ->get();

        if ($requirements->isEmpty()) {
            $requirements = (clone $base)
                ->whereNull('country')
                ->orderBy('order')
                ->get();
        }

        $documents = $contract->documents()->get();

        return $requirements->map(function (DocumentRequirement $requirement) use ($documents) {
            $doc = $documents->firstWhere('requirement_key', $requirement->key);

            return [
                'requirement' => $requirement,
                'uploaded' => $doc !== null,
                'validated' => $doc?->status === 'validated',
                'document' => $doc,
            ];
        });
    }

    public function completeness(Contract $contract): array
    {
        $checklist = $this->checklist($contract);

        $total = $checklist->where('requirement.mandatory', true)->count();
        $done = $checklist->where('requirement.mandatory', true)->where('uploaded', true)->count();

        return [
            'total' => $total,
            'done' => $done,
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 100,
            'complete' => $total > 0 && $done >= $total,
        ];
    }
}
