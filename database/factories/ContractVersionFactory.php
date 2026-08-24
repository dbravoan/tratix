<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractVersion>
 */
class ContractVersionFactory extends Factory
{
    protected $model = ContractVersion::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'version' => 1,
            'clauses' => [['key' => 'objeto', 'title' => 'Objeto', 'body' => 'Descripción.']],
            'changes_summary' => null,
            'hash' => str_repeat('0', 64),
            'pdf_path' => null,
            'frozen_at' => now(),
        ];
    }
}
