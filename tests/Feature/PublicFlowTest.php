<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use App\Services\TsaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Fakes\FakeTsa;
use Tests\TestCase;

class PublicFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->app->instance(TsaService::class, new FakeTsa);
    }

    private function makeContract(): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'title' => 'Flujo público',
            'object_type' => 'Mueble',
            'object_description' => 'Silla.',
            'quantity' => 1,
            'price_amount' => 300,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana García',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'C/ Mayor 1', 'postal_code' => '28001', 'city' => 'Madrid',
            'email' => 'ana@ejemplo.com',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'Av. Paz 2', 'postal_code' => '28002', 'city' => 'Madrid',
            'email' => 'luis@ejemplo.com',
        ]);
    }

    public function test_sign_page_is_public_when_contract_in_signing_phase(): void
    {
        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('sign.show', $token), ['Accept-Language' => 'es'])->assertOk()->assertSee('Proceso de Firma Electrónica');
    }

    public function test_sign_page_denied_for_draft(): void
    {
        $contract = $this->makeContract();
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('sign.show', $token))->assertForbidden();
    }

    public function test_full_public_signing_flow(): void
    {
        config(['signing.otp_enabled' => false]);

        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->post(route('sign.store', $token), [
            'role' => 'comprador',
            'signer_name' => 'Luis Pérez',
            'signer_email' => 'luis@ejemplo.com',
            'signature_type' => 'fes-click',
            'signature_image' => '',
            'consent' => '1',
        ])->assertRedirect();

        $this->post(route('sign.store', $token), [
            'role' => 'vendedor',
            'signer_name' => 'Ana García',
            'signer_email' => 'ana@ejemplo.com',
            'signature_type' => 'fes-click',
            'signature_image' => '',
            'consent' => '1',
        ])->assertRedirect();

        $contract->refresh();
        $this->assertSame('firmado', $contract->status);
        $this->assertNotNull($contract->final_hash);
        $this->assertDatabaseHas('consents', ['contract_id' => $contract->id, 'consent_type' => 'signing']);
    }

    public function test_signing_requires_otp_when_enabled(): void
    {
        config(['signing.otp_enabled' => true]);

        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        // Missing OTP → rejected.
        $this->post(route('sign.store', $token), [
            'role' => 'comprador',
            'signer_name' => 'Luis Pérez',
            'signer_email' => 'luis@ejemplo.com',
            'signature_type' => 'fes-click',
            'signature_image' => '',
            'consent' => '1',
        ])->assertSessionHasErrors('otp_code');

        // Wrong OTP → rejected.
        Cache::put('sign_otp:'.$token.':luis@ejemplo.com', '123456', now()->addMinutes(5));
        $this->post(route('sign.store', $token), [
            'role' => 'comprador',
            'signer_name' => 'Luis Pérez',
            'signer_email' => 'luis@ejemplo.com',
            'signature_type' => 'fes-click',
            'signature_image' => '',
            'consent' => '1',
            'otp_code' => '000000',
        ])->assertSessionHas('error');

        // Correct OTP → signature recorded as FEA-verified.
        Cache::put('sign_otp:'.$token.':luis@ejemplo.com', '123456', now()->addMinutes(5));
        $this->post(route('sign.store', $token), [
            'role' => 'comprador',
            'signer_name' => 'Luis Pérez',
            'signer_email' => 'luis@ejemplo.com',
            'signature_type' => 'fes-click',
            'signature_image' => '',
            'consent' => '1',
            'otp_code' => '123456',
        ])->assertRedirect();

        $this->assertDatabaseHas('signatures', [
            'contract_id' => $contract->id,
            'party_role' => 'comprador',
            'otp_verified' => true,
        ]);
    }

    public function test_sign_page_shows_rights_and_obligations(): void
    {
        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('sign.show', $token), ['Accept-Language' => 'es'])
            ->assertOk()
            ->assertSee('Derechos y obligaciones de las partes')
            ->assertSee('Como VENDEDOR')
            ->assertSee('Como COMPRADOR');
    }

    public function test_review_accept_records_audit(): void
    {
        $contract = $this->makeContract();
        $contract = app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->get(route('review.show', $token))->assertOk()->assertSee('Aceptación del Borrador');

        $this->post(route('review.accept', $token), [
            'role' => 'comprador',
            'acceptor_name' => 'Luis Pérez',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_trail', [
            'contract_id' => $contract->id,
            'event' => 'review_accepted',
            'actor' => 'comprador',
        ]);
    }

    public function test_review_propose_creates_pending_proposal(): void
    {
        $contract = $this->makeContract();
        $contract = app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->post(route('review.propose', $token), [
            'role' => 'comprador',
            'clause_key' => 'precio',
            'proposed_text' => 'El precio se pagará en dos plazos.',
            'reason' => 'Facilidades de pago.',
        ])->assertRedirect();

        $this->assertDatabaseHas('clause_proposals', [
            'contract_id' => $contract->id,
            'status' => 'pending',
            'proposed_by' => 'comprador',
        ]);
    }

    public function test_review_page_renders_clean_stacked_layout(): void
    {
        $contract = $this->makeContract();
        $contract = app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $response = $this->get(route('review.show', $token));
        $response->assertOk();
        $response->assertSee('Revisión Colaborativa');
        $response->assertSee('Descargar borrador en PDF');
        $response->assertSee('Estás revisando este contrato como la parte invitada:');
        $response->assertSee('Datos de la Parte Creadora');
        $response->assertSee('Tus Datos de Identificación');
        $response->assertSee('Escáner de Documento (DNI / NIE / Pasaporte)');
    }

    public function test_party_can_update_own_legal_details_during_review(): void
    {
        $contract = $this->makeContract();
        $contract = app(ContractWorkflowService::class)->transition($contract, 'en_revision', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->post(route('review.party.update', $token), [
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Luis Pérez Modificado',
            'tax_id' => '00000000T',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Nueva Calle 123',
            'postal_code' => '46001',
            'city' => 'Valencia',
            'phone' => '+34 699 999 999',
            'email' => 'luis.nuevo@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('parties', [
            'contract_id' => $contract->id,
            'role' => 'comprador',
            'full_name' => 'Luis Pérez Modificado',
            'tax_id' => '00000000T',
            'city' => 'Valencia',
        ]);

        $this->assertDatabaseHas('audit_trail', [
            'contract_id' => $contract->id,
            'event' => 'party_updated',
        ]);
    }

    public function test_sign_show_page_renders_after_both_parties_sign_without_error(): void
    {
        config(['signing.otp_enabled' => false]);

        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        $this->post(route('sign.store', $token), [
            'role' => 'comprador',
            'signer_name' => 'Luis Pérez',
            'signer_email' => 'luis@ejemplo.com',
            'signature_type' => 'fes-click',
            'consent' => '1',
        ]);

        $this->post(route('sign.store', $token), [
            'role' => 'vendedor',
            'signer_name' => 'Ana García',
            'signer_email' => 'ana@ejemplo.com',
            'signature_type' => 'fes-click',
            'consent' => '1',
        ]);

        // Access sign.show after both signed (user mentioned 500 error on BadMethodCallException allPartiesSigned)
        $this->get(route('sign.show', $token))
            ->assertOk()
            ->assertSee('Descargar PDF final firmado y sellado');
    }

    public function test_collaborative_creation_with_delegated_counterparty(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        // Creator (vendedor) creates contract with delegated buyer (only email provided)
        $response = $this->actingAs($user)->post(route('contracts.store'), [
            'contract_type' => 'bienes_muebles',
            'title' => 'Contrato colaborativo',
            'object_type' => 'Maquinaria',
            'object_description' => 'Torno industrial CNC',
            'quantity' => 1,
            'price_amount' => 5000,
            'currency' => 'EUR',
            'city' => 'Bilbao',
            'signing_date' => now()->toDateString(),
            'creator_role' => 'vendedor',
            'invite_counterparty_to_fill' => '1',
            'seller' => [
                'party_type' => 'particular',
                'full_name' => 'Carlos Vendedor',
                'tax_id' => '12345678Z',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Gran Vía 1',
                'postal_code' => '48001',
                'city' => 'Bilbao',
                'email' => 'carlos@vendedor.es',
            ],
            'buyer' => [
                'email' => 'marta@comprador.es',
                'phone' => '+34655443322',
            ],
        ]);

        $contract = Contract::latest()->first();
        $this->assertNotNull($contract);
        $token = $contract->access_token;

        // Counterparty (buyer) completes her legal data via review link
        $this->post(route('review.party.update', $token), [
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Marta Compradora',
            'tax_id' => '87654321X',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Mayor 10',
            'postal_code' => '48002',
            'city' => 'Bilbao',
            'email' => 'marta@comprador.es',
        ])->assertRedirect();

        $contract->refresh();
        $this->assertEquals('Marta Compradora', $contract->buyer()->full_name);
        $this->assertEquals('87654321X', $contract->buyer()->tax_id);
    }

    public function test_signature_strictly_enforces_party_email_and_role(): void
    {
        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');
        $token = app(SignatureService::class)->ensureToken($contract)->access_token;

        // Try to request OTP for vendedor using a forged external email
        $this->post(route('sign.otp', $token), [
            'role' => 'vendedor',
            'signer_email' => 'hacker@forged.com',
        ])->assertSessionHas('error');

        // Try to sign as vendedor with a mismatching email
        $this->post(route('sign.store', $token), [
            'role' => 'vendedor',
            'signer_name' => 'Ana García',
            'signer_email' => 'fake-email@random.com',
            'signature_type' => 'fes-click',
            'consent' => '1',
            'otp_code' => '123456',
        ])->assertSessionHas('error');
    }
}
