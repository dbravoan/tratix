<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Party>
 */
class PartyFactory extends Factory
{
    protected $model = Party::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => fake()->name(),
            'company_name' => null,
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'province' => null,
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'activity' => null,
            'representative_name' => null,
            'representative_tax_id' => null,
            'eori' => null,
            'registered_vat' => false,
            'acting_in_own_name' => true,
            'signature_city' => null,
            'signature_date' => null,
        ];
    }

    public function professional(): static
    {
        return $this->state(fn () => [
            'party_type' => 'sociedad',
            'full_name' => null,
            'company_name' => fake()->company(),
            'tax_id' => 'B12345679',
        ]);
    }

    public function europeanVat(string $country, string $taxId, bool $registered = true): static
    {
        return $this->state(fn () => [
            'country' => $country,
            'tax_id_country' => $country,
            'tax_id' => $taxId,
            'registered_vat' => $registered,
        ]);
    }
}
