<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractLegalValidator;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractCreationTest extends TestCase
{
    use RefreshDatabase;

    private function buildContract(array $sellerAttrs = [], array $buyerAttrs = [], array $contractAttrs = []): Contract
    {
        $service = app(ContractService::class);

        $seller = array_merge([
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => 'Ana García',
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Mayor 1',
            'postal_code' => '28001',
            'city' => 'Madrid',
        ], $sellerAttrs);

        $buyer = array_merge([
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Av. de la Paz 2',
            'postal_code' => '28002',
            'city' => 'Madrid',
        ], $buyerAttrs);

        $data = array_merge([
            'contract_type' => 'bienes_muebles',
            'title' => 'Compraventa de prueba',
            'object_type' => 'Bien mueble',
            'object_description' => 'Una mesa de madera de roble.',
            'quantity' => 1,
            'price_amount' => 1000,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
        ], $contractAttrs);

        return $service->create($data, $seller, $buyer);
    }

    public function test_c2c_contract_is_created_with_resolved_regime_and_clauses(): void
    {
        $contract = $this->buildContract();

        $this->assertSame('c2c', $contract->transaction_type);
        $this->assertSame('nacional', $contract->jurisdiction);
        $this->assertSame(1000.0, (float) $contract->total_amount);
        $this->assertNotEmpty($contract->clauses);
        $this->assertCount(2, $contract->parties);
    }

    public function test_b2b_intracomunitario_is_detected(): void
    {
        $contract = $this->buildContract(
            sellerAttrs: ['party_type' => 'sociedad', 'company_name' => 'Vende SL', 'tax_id' => 'B12345679', 'registered_vat' => true],
            buyerAttrs: ['party_type' => 'sociedad', 'company_name' => 'Kauft GmbH', 'tax_id' => 'DE123456789', 'tax_id_country' => 'DE', 'country' => 'DE', 'registered_vat' => true],
        );

        $this->assertSame('b2b', $contract->transaction_type);
        $this->assertSame('intracomunitario', $contract->jurisdiction);
        $this->assertStringContainsString('inversión del sujeto pasivo', $contract->legal_notes);
    }

    public function test_b2c_requires_consumer_protection_wording(): void
    {
        $contract = $this->buildContract(
            sellerAttrs: ['party_type' => 'sociedad', 'company_name' => 'Vende SL', 'tax_id' => 'B12345679'],
            buyerAttrs: ['party_type' => 'particular', 'full_name' => 'Luis Pérez', 'tax_id' => '87654321X'],
            contractAttrs: ['contract_type' => 'servicios'],
        );

        $validator = app(ContractLegalValidator::class);
        $issues = $validator->validate($contract);

        $hasWithdrawal = collect($issues)->contains(fn ($i) => str_contains($i['message'], 'desistimiento') && $i['level'] === 'error');

        $this->assertSame('b2c', $contract->transaction_type);
        $this->assertTrue($hasWithdrawal, 'A B2C distance contract must demand the 14-day withdrawal clause.');
    }

    public function test_valid_contract_passes_validation(): void
    {
        $contract = $this->buildContract();

        $result = app(ContractService::class)->validate($contract);

        $this->assertEmpty(array_filter($result['issues'], fn ($i) => $i['level'] === 'error'));
    }

    public function test_contract_with_bad_tax_id_is_invalid(): void
    {
        $contract = $this->buildContract(
            buyerAttrs: ['tax_id' => '87654321A'], // wrong control letter
        );

        $result = app(ContractService::class)->validate($contract);
        $issues = $result['issues'];

        $this->assertNotEmpty(array_filter($issues, fn ($i) => $i['level'] === 'error' && str_contains($i['message'], 'no es válido')));
    }

    public function test_intracomunitario_b2b_requires_vat_registration(): void
    {
        $contract = $this->buildContract(
            sellerAttrs: ['party_type' => 'sociedad', 'company_name' => 'Vende SL', 'tax_id' => 'B12345679', 'registered_vat' => true],
            buyerAttrs: ['party_type' => 'sociedad', 'company_name' => 'Kauft GmbH', 'tax_id' => 'DE123456789', 'tax_id_country' => 'DE', 'country' => 'DE', 'registered_vat' => false],
        );

        $issues = app(ContractLegalValidator::class)->validate($contract);

        $this->assertTrue(collect($issues)->contains(fn ($i) => str_contains($i['message'], 'ROI')));
    }

    public function test_contract_can_be_created_through_http(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('contracts.store'), [
            'contract_type' => 'bienes_muebles',
            'creator_role' => 'vendedor',
            'title' => 'Contrato HTTP',
            'object_type' => 'Mueble',
            'object_description' => 'Descripción.',
            'quantity' => 1,
            'price_amount' => 500,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Sevilla',
            'signing_date' => now()->toDateString(),
            'seller' => [
                'party_type' => 'particular', 'full_name' => 'Ana', 'tax_id' => '12345678Z',
                'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'C/ Uno 1',
                'postal_code' => '41001', 'city' => 'Sevilla',
            ],
            'buyer' => [
                'party_type' => 'particular', 'full_name' => 'Luis', 'tax_id' => '87654321X',
                'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'C/ Dos 2',
                'postal_code' => '41001', 'city' => 'Sevilla',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', ['title' => 'Contrato HTTP']);
    }
}
