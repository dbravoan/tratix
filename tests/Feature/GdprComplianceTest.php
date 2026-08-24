<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Party;
use App\Models\User;
use App\Services\ClauseBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GdprComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_page_is_complete_and_contains_mandatory_gdpr_sections(): void
    {
        $response = $this->get(route('privacy'));

        $response->assertOk()
            ->assertSee('Política de Privacidad y Protección de Datos')
            ->assertSee('RGPD (UE) 2016/679')
            ->assertSee('LOPDGDD 3/2018')
            ->assertSee('Responsable del Tratamiento')
            ->assertSee('Agencia Española de Protección de Datos (AEPD)')
            ->assertSee('Bloqueo legal de datos')
            ->assertSee('Derecho a la Portabilidad (Art. 20)');
    }

    public function test_user_can_export_complete_personal_data_under_gdpr_article_20(): void
    {
        $user = User::factory()->create([
            'name' => 'María García',
            'email' => 'maria@ejemplo.com',
            'company_name' => 'García Consultores SL',
            'tax_id' => 'B12345678',
            'phone' => '+34611223344',
            'address' => 'Paseo de la Castellana 50',
            'postal_code' => '28046',
            'city' => 'Madrid',
            'country' => 'ES',
        ]);

        $contract = Contract::factory()->create([
            'user_id' => $user->id,
            'title' => 'Contrato de Servicios Informáticos',
            'status' => 'firmado',
        ]);

        $response = $this->actingAs($user)->get(route('profile.gdpr.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rgpd_export_metadata', $data);
        $this->assertArrayHasKey('user_account', $data);
        $this->assertArrayHasKey('fiscal_profile', $data);
        $this->assertArrayHasKey('contracts', $data);

        $this->assertEquals('maria@ejemplo.com', $data['user_account']['email']);
        $this->assertEquals('B12345678', $data['fiscal_profile']['tax_id']);
        $this->assertCount(1, $data['contracts']);
        $this->assertEquals($contract->reference, $data['contracts'][0]['reference']);
    }

    public function test_user_can_submit_formal_gdpr_rights_exercise_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.gdpr.request'), [
            'right_type' => 'rectificacion',
            'description' => 'Solicito rectificación de mi domicilio social por traslado a Calle Gran Vía 10.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_contract_clause_builder_includes_comprehensive_gdpr_clause(): void
    {
        $builder = app(ClauseBuilder::class);

        $contract = Contract::factory()->make([
            'contract_type' => 'servicios',
            'applicable_law' => 'ES',
        ]);
        $seller = Party::factory()->make(['role' => 'vendedor', 'tax_id_country' => 'ES', 'country' => 'ES']);
        $buyer = Party::factory()->make(['role' => 'comprador', 'tax_id_country' => 'ES', 'country' => 'ES']);

        $resolution = [
            'transaction_type' => 'b2b',
            'jurisdiction' => 'nacional',
            'intracomunitario_b2b' => false,
            'vat_notes' => 'IVA nacional',
        ];

        $clauses = $builder->build($contract, $seller, $buyer, $resolution);

        $gdprClause = collect($clauses)->firstWhere('key', 'proteccion_datos');

        $this->assertNotNull($gdprClause);
        $this->assertStringContainsString('Reglamento General de Protección de Datos (RGPD UE 2016/679)', $gdprClause['body']);
        $this->assertStringContainsString('art. 6.1.b RGPD', $gdprClause['body']);
        $this->assertStringContainsString('Agencia Española de Protección de Datos - AEPD', $gdprClause['body']);
        $this->assertStringContainsString('bloqueados', $gdprClause['body']);
    }

    public function test_purge_orphan_scans_command_deletes_unlinked_old_files(): void
    {
        Storage::fake('local');

        // Create an old orphan file
        Storage::disk('local')->put('documents/temp_scans/orphan_old.png', 'temp-scan-content');

        // Create a new orphan file
        Storage::disk('local')->put('documents/temp_scans/orphan_new.png', 'temp-scan-content');

        // Artificially change last modified timestamp using touch on real/fake disk if supported,
        // or test command execution with hours=0
        $this->artisan('contracts:purge-orphan-scans', ['--hours' => 0])
            ->expectsOutputToContain('Purged 2 orphaned temporary scan files')
            ->assertExitCode(0);

        $this->assertFalse(Storage::disk('local')->exists('documents/temp_scans/orphan_old.png'));
        $this->assertFalse(Storage::disk('local')->exists('documents/temp_scans/orphan_new.png'));
    }
}
