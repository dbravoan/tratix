<?php

namespace Database\Factories;

use App\Models\ClauseProposal;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClauseProposal>
 */
class ClauseProposalFactory extends Factory
{
    protected $model = ClauseProposal::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'contract_version_id' => null,
            'clause_key' => 'objeto',
            'clause_title' => 'Objeto del contrato',
            'original_text' => 'Texto original.',
            'proposed_text' => 'Texto propuesto.',
            'proposed_by' => 'buyer',
            'reason' => null,
            'status' => 'pending',
        ];
    }
}
