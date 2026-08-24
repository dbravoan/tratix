<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'reference' => 'C-'.now()->format('Y').'-'.fake()->unique()->numberBetween(1000, 9999),
            'contract_type' => 'bienes_muebles',
            'transaction_type' => 'b2c',
            'jurisdiction' => 'nacional',
            'title' => 'Contrato de compraventa de prueba',
            'object_type' => 'Bien mueble',
            'object_description' => 'Un bien mueble de prueba.',
            'quantity' => 1,
            'price_amount' => 1000.00,
            'currency' => 'EUR',
            'tax_amount' => 210.00,
            'total_amount' => 1210.00,
            'city' => 'Madrid',
            'signing_date' => now(),
            'effective_date' => null,
            'delivery_terms' => null,
            'payment_terms' => null,
            'warranties' => null,
            'special_clauses' => null,
            'clauses' => null,
            'status' => 'borrador',
            'language' => 'es',
            'legal_notes' => null,
        ];
    }
}
