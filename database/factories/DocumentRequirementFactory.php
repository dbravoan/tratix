<?php

namespace Database\Factories;

use App\Models\DocumentRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentRequirement>
 */
class DocumentRequirementFactory extends Factory
{
    protected $model = DocumentRequirement::class;

    public function definition(): array
    {
        return [
            'contract_type' => 'vehiculos',
            'transaction_type' => 'c2c',
            'jurisdiction' => 'nacional',
            'order' => 1,
            'key' => 'contrato_firmado',
            'title' => 'Contrato firmado',
            'purpose' => 'Es el documento principal.',
            'steps' => null,
            'legal_note' => null,
            'link_label' => null,
            'link_url' => null,
            'mandatory' => true,
        ];
    }
}
