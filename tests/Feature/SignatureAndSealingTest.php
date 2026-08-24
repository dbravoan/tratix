<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\SignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Fakes\FakeTsa;
use Tests\TestCase;

class SignatureAndSealingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function makeContract(): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'title' => 'Compraventa para firmar',
            'object_type' => 'Mueble',
            'object_description' => 'Mesa de roble maciza.',
            'quantity' => 1,
            'price_amount' => 1000,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Ana García',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'C/ Mayor 1', 'postal_code' => '28001', 'city' => 'Madrid',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Luis Pérez',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'address' => 'Av. Paz 2', 'postal_code' => '28002', 'city' => 'Madrid',
        ]);
    }

    private function contractInSigningPhase(): Contract
    {
        $contract = $this->makeContract();
        $workflow = app(ContractWorkflowService::class);
        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator');
        $contract = $workflow->transition($contract, 'en_firma', 'creator');

        return $contract;
    }

    public function test_token_is_generated_and_link_buildable(): void
    {
        $contract = $this->makeContract();
        $service = app(SignatureService::class);

        $link = $service->signingLink($contract);

        $this->assertStringContainsString('/sign/', $link);
        $this->assertNotNull($contract->fresh()->access_token);
    }

    public function test_single_signature_does_not_seal(): void
    {
        $this->app->instance(TsaService::class, new FakeTsa);
        $contract = $this->contractInSigningPhase();
        $service = app(SignatureService::class);

        $service->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent');

        $contract->refresh();

        $this->assertSame('en_firma', $contract->status);
        $this->assertTrue($service->partyHasSigned($contract, 'comprador'));
        $this->assertFalse($service->allPartiesSigned($contract));
        $this->assertNull($contract->final_hash);
    }

    public function test_both_signatures_seal_contract(): void
    {
        $this->app->instance(TsaService::class, new FakeTsa);
        $contract = $this->contractInSigningPhase();
        $service = app(SignatureService::class);

        $service->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent');
        $contract->refresh();
        $service->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-canvas', null, 'consent');

        $contract->refresh();

        $this->assertSame('firmado', $contract->status);
        $this->assertNotNull($contract->final_hash);
        $this->assertSame(64, strlen($contract->final_hash));
        $this->assertNotNull($contract->sealed_at);
        $this->assertNotNull($contract->final_pdf_path);
        $this->assertSame(2, $contract->signatures()->count());

        Storage::disk('local')->assertExists($contract->final_pdf_path);
    }

    public function test_duplicate_signature_is_rejected(): void
    {
        $this->app->instance(TsaService::class, new FakeTsa);
        $contract = $this->contractInSigningPhase();
        $service = app(SignatureService::class);

        $service->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-click', null, 'consent');

        $this->expectException(\DomainException::class);
        $service->sign($contract, 'vendedor', 'Otra Ana', 'otra@ejemplo.com', 'fes-click', null, 'consent');
    }

    public function test_integrity_verification_detects_tampering(): void
    {
        $this->app->instance(TsaService::class, new FakeTsa);
        $contract = $this->contractInSigningPhase();
        $service = app(SignatureService::class);

        $service->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent');
        $contract->refresh();
        $service->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-click', null, 'consent');
        $contract->refresh();

        $result = app(ContractService::class)->verifyIntegrity($contract);
        $this->assertTrue($result['valid']);

        // Tamper with the file
        $disk = Storage::disk('local');
        $disk->put($contract->final_pdf_path, 'tampered content');

        $result = app(ContractService::class)->verifyIntegrity($contract);
        $this->assertFalse($result['valid']);
    }

    public function test_evidence_files_are_stored(): void
    {
        $this->app->instance(TsaService::class, new FakeTsa);
        $contract = $this->contractInSigningPhase();
        $service = app(SignatureService::class);

        $service->sign($contract, 'comprador', 'Luis Pérez', 'luis@ejemplo.com', 'fes-click', null, 'consent');
        $contract->refresh();
        $service->sign($contract, 'vendedor', 'Ana García', 'ana@ejemplo.com', 'fes-click', null, 'consent');
        $contract->refresh();

        $base = 'contracts/'.$contract->reference;
        Storage::disk('local')->assertExists($base.'/evidence-payload.txt');
        Storage::disk('local')->assertExists($base.'/evidence-tsr.txt');

        $payload = Storage::disk('local')->get($base.'/evidence-payload.txt');
        $this->assertStringContainsString('REFERENCE: '.$contract->reference, $payload);
        $this->assertStringContainsString('FINAL_PDF_SHA256: '.$contract->final_hash, $payload);
    }
}
