<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CountryAndBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function makeLatamContract(): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'title' => 'Compraventa México',
            'object_type' => 'Mueble',
            'object_description' => 'Mesa.',
            'quantity' => 1,
            'price_amount' => 12000,
            'currency' => 'MXN',
            'tax_amount' => 0,
            'city' => 'CDMX',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Juan López',
            'tax_id' => 'LOPJ800101HDFRRL09', 'tax_id_country' => 'MX', 'country' => 'MX',
            'address' => 'Av. Reforma 1', 'postal_code' => '06600', 'city' => 'CDMX',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'María Díaz',
            'tax_id' => 'DIMR900202HDFXRL07', 'tax_id_country' => 'MX', 'country' => 'MX',
            'address' => 'Calle Luna 2', 'postal_code' => '06700', 'city' => 'CDMX',
        ]);
    }

    public function test_latam_contract_resolves_country_and_regime(): void
    {
        $contract = $this->makeLatamContract();

        $this->assertSame('MX', $contract->applicable_law);
        $this->assertSame('c2c', $contract->transaction_type);
        $this->assertSame('nacional', $contract->jurisdiction);
        $this->assertStringContainsString('IVA', (string) $contract->legal_notes);
    }

    public function test_latam_contract_has_rights_obligations_clause(): void
    {
        $contract = $this->makeLatamContract();

        $clauses = collect($contract->clauses);
        $ro = $clauses->firstWhere('key', 'derechos_obligaciones');

        $this->assertNotNull($ro);
        $this->assertStringContainsString('México', (string) $ro['body']);
        $this->assertStringContainsString('DERECHOS', (string) $ro['body']);
        $this->assertStringContainsString('OBLIGACIONES', (string) $ro['body']);
    }

    public function test_latam_contract_uses_country_legal_references(): void
    {
        $contract = $this->makeLatamContract();
        $clauses = collect($contract->clauses);

        $jurisdiction = $clauses->firstWhere('key', 'jurisdiccion');
        $consumer = $clauses->firstWhere('key', 'derechos_consumidor');
        $this->assertNull($consumer); // c2c has no consumer clause

        $ro = $clauses->firstWhere('key', 'derechos_obligaciones');
        $this->assertStringContainsString('Ley Federal de Protección al Consumidor', (string) $ro['body']);
        $this->assertStringContainsString('Código Civil Federal', (string) $jurisdiction['body']);
    }

    public function test_argentina_contract_uses_cuit_and_ar_references(): void
    {
        $contract = app(ContractService::class)->create([
            'contract_type' => 'servicios',
            'title' => 'Servicios Argentina',
            'object_type' => 'Servicio',
            'object_description' => 'Desarrollo web.',
            'quantity' => 1,
            'price_amount' => 5000,
            'currency' => 'ARS',
            'tax_amount' => 0,
            'city' => 'Buenos Aires',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'sociedad', 'full_name' => 'Estudio Digital SA',
            'company_name' => 'Estudio Digital SA', 'tax_id' => '30-12345678-9', 'tax_id_country' => 'AR', 'country' => 'AR', 'activity' => 'Servicios informáticos',
            'address' => 'Av. Corrientes 500', 'postal_code' => '1043', 'city' => 'Buenos Aires',
        ], [
            'role' => 'comprador', 'party_type' => 'sociedad', 'full_name' => 'Comercial Norte SA',
            'company_name' => 'Comercial Norte SA', 'tax_id' => '30-87654321-8', 'tax_id_country' => 'AR', 'country' => 'AR', 'activity' => 'Comercio',
            'address' => 'Calle Florida 300', 'postal_code' => '1005', 'city' => 'Buenos Aires',
        ]);

        $this->assertSame('AR', $contract->applicable_law);
        $this->assertSame('b2b', $contract->transaction_type);
        $clauses = collect($contract->clauses);
        $this->assertStringContainsString('Argentina', (string) $clauses->firstWhere('key', 'jurisdiccion')['body']);
    }

    public function test_free_plan_credit_limit_blocks_creation(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $credits = app(CreditService::class);

        $this->assertTrue($credits->canCreate($user));

        // Consume the free monthly quota.
        for ($i = 0; $i < $credits->monthlyLimit($user); $i++) {
            Contract::factory()->create(['user_id' => $user->id, 'status' => 'borrador']);
        }

        $this->assertFalse($credits->canCreate($user));

        // Pro plan is unlimited.
        $user->update(['plan' => 'pro']);
        $this->assertTrue($credits->canCreate($user));
    }

    public function test_http_creation_is_blocked_when_credits_exhausted(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        for ($i = 0; $i < app(CreditService::class)->monthlyLimit($user); $i++) {
            Contract::factory()->create(['user_id' => $user->id, 'status' => 'borrador']);
        }

        $response = $this->post(route('contracts.store'), [
            'contract_type' => 'bienes_muebles', 'creator_role' => 'vendedor', 'title' => 'Bloqueado', 'object_type' => 'Mueble',
            'object_description' => 'X.', 'quantity' => 1, 'price_amount' => 1, 'currency' => 'EUR',
            'tax_amount' => 0, 'city' => 'Madrid', 'signing_date' => now()->toDateString(),
            'seller' => ['party_type' => 'particular', 'full_name' => 'A', 'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'a', 'postal_code' => '28001', 'city' => 'Madrid'],
            'buyer' => ['party_type' => 'particular', 'full_name' => 'B', 'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES', 'address' => 'b', 'postal_code' => '28002', 'city' => 'Madrid'],
        ]);

        $response->assertRedirect(route('billing.pricing'));
        $this->assertDatabaseCount('contracts', app(CreditService::class)->monthlyLimit($user));
    }

    public function test_demo_billing_upgrades_to_pro(): void
    {
        config(['billing.gateway' => 'demo']);
        $user = User::factory()->create(['email_verified_at' => now(), 'plan' => 'free']);
        $this->actingAs($user);

        $this->post(route('billing.checkout'), ['plan' => 'pro'])->assertRedirect();

        $this->assertSame('pro', $user->fresh()->plan);
    }

    public function test_admin_panel_requires_admin(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_admin' => false]);
        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();

        $admin = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.index'))->assertOk()->assertSee('Panel de administración');
    }

    public function test_privacy_page_is_public(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Política de privacidad');
    }
}
