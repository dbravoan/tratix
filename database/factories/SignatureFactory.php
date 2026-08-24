<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Signature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Signature>
 */
class SignatureFactory extends Factory
{
    protected $model = Signature::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'contract_version_id' => null,
            'party_id' => null,
            'party_role' => 'vendedor',
            'signer_name' => fake()->name(),
            'signer_email' => fake()->safeEmail(),
            'signature_type' => 'fes-click',
            'signature_image_path' => null,
            'signed_at' => now(),
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'latitude' => null,
            'longitude' => null,
            'consent_text' => 'Consentimiento de prueba.',
        ];
    }
}
