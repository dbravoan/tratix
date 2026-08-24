<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewContractTypesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function baseCreate(array $overrides = [], array $seller = [], array $buyer = []): Contract
    {
        $seller = array_merge([
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana García',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid',
        ], $seller);

        $buyer = array_merge([
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid',
        ], $buyer);

        return app(ContractService::class)->create(array_merge([
            'contract_type' => 'alquiler',
            'creator_role' => 'vendedor',
            'title' => 'Contrato nuevo tipo',
            'object_type' => 'Vivienda',
            'object_description' => 'Vivienda en alquiler.',
            'quantity' => 1,
            'price_amount' => 900,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
        ], $overrides), $seller, $buyer);
    }

    public function test_alquiler_generates_rent_and_arrendamiento_clauses(): void
    {
        $contract = $this->baseCreate();
        $clauses = collect($contract->clauses);

        $this->assertSame('alquiler', $contract->contract_type);
        $this->assertNotNull($clauses->firstWhere('key', 'objeto'));
        $this->assertStringContainsString('cede el uso y disfrute', (string) $clauses->firstWhere('key', 'objeto')['body']);

        $rent = $clauses->firstWhere('key', 'precio');
        $this->assertStringContainsString('Renta', (string) $rent['title']);

        $arrendamiento = $clauses->firstWhere('key', 'arrendamiento');
        $this->assertNotNull($arrendamiento);
        $this->assertStringContainsString('subarrendar', (string) $arrendamiento['body']);

        // No generic sale clauses for non-sale types.
        $this->assertNull($clauses->firstWhere('key', 'garantias'));
    }

    public function test_prestamo_generates_loan_clauses(): void
    {
        $contract = $this->baseCreate(['contract_type' => 'prestamo', 'price_amount' => 5000, 'title' => 'Préstamo']);
        $clauses = collect($contract->clauses);

        $objeto = $clauses->firstWhere('key', 'objeto');
        $this->assertStringContainsString('entrega en préstamo', (string) $objeto['body']);

        $precio = $clauses->firstWhere('key', 'precio');
        $this->assertStringContainsString('Importe y devolución', (string) $precio['title']);

        $obligaciones = $clauses->firstWhere('key', 'obligaciones_prestatario');
        $this->assertNotNull($obligaciones);
    }

    public function test_cesion_derechos_generates_cesion_clause(): void
    {
        $contract = $this->baseCreate(['contract_type' => 'cesion_derechos']);
        $clauses = collect($contract->clauses);

        $objeto = $clauses->firstWhere('key', 'objeto');
        $this->assertStringContainsString('transmite a favor', (string) $objeto['body']);

        $this->assertNotNull($clauses->firstWhere('key', 'cesion'));
    }

    public function test_nda_generates_confidentiality_clause(): void
    {
        $contract = $this->baseCreate(['contract_type' => 'nda', 'transaction_type' => 'b2b']);
        $clauses = collect($contract->clauses);

        $objeto = $clauses->firstWhere('key', 'objeto');
        $this->assertStringContainsString('confidencialidad', (string) $objeto['body']);

        $conf = $clauses->firstWhere('key', 'confidencialidad');
        $this->assertNotNull($conf);
        $this->assertStringContainsString('no divulgar', (string) $conf['body']);

        $precio = $clauses->firstWhere('key', 'precio');
        $this->assertStringContainsString('no implica contraprestación', (string) $precio['body']);
    }

    public function test_all_new_types_are_valid_contract_types(): void
    {
        foreach (['alquiler', 'prestamo', 'cesion_derechos', 'nda'] as $type) {
            $this->assertContains($type, Contract::TYPES);
        }
    }

    public function test_alquiler_can_be_created_through_http(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('contracts.store'), [
            'contract_type' => 'alquiler', 'creator_role' => 'vendedor', 'title' => 'Alquiler HTTP',
            'object_type' => 'Piso', 'object_description' => 'Piso amueblado.', 'quantity' => 1,
            'price_amount' => 850, 'currency' => 'EUR', 'tax_amount' => 0,
            'city' => 'Valencia', 'signing_date' => now()->toDateString(),
            'seller' => ['party_type' => 'particular', 'full_name' => 'Ana', 'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '46001', 'city' => 'Valencia'],
            'buyer' => ['party_type' => 'particular', 'full_name' => 'Luis', 'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '46002', 'city' => 'Valencia'],
        ])->assertRedirect();

        $this->assertDatabaseHas('contracts', ['contract_type' => 'alquiler', 'title' => 'Alquiler HTTP']);
    }
}
