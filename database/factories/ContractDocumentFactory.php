<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDocument>
 */
class ContractDocumentFactory extends Factory
{
    protected $model = ContractDocument::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'requirement_key' => 'dni_partes',
            'filename' => 'dni.pdf',
            'path' => 'documents/test/dni.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
            'status' => 'uploaded',
            'uploaded_by_user_id' => null,
            'uploaded_at' => now(),
            'validated_at' => null,
        ];
    }
}
