<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\DocumentRequirement;
use App\Models\User;
use App\Services\ContractService;
use App\Services\DocumentGuidanceService;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentGuidanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(DocumentRequirementSeeder::class);
    }

    private function makeVehicleContract(?int $ownerId = null): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'vehiculos',
            'title' => 'Compraventa de vehículo',
            'object_type' => 'Vehículo',
            'object_description' => 'Coche usado.',
            'quantity' => 1,
            'price_amount' => 9000,
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
        ], $ownerId);
    }

    public function test_seeder_populates_vehicle_checklist(): void
    {
        $this->assertGreaterThan(8, DocumentRequirement::where('contract_type', 'vehiculos')->where('transaction_type', 'c2c')->count());
        $this->assertDatabaseHas('document_requirements', [
            'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'key' => 'cambio_titularidad',
        ]);
        $this->assertDatabaseHas('document_requirements', [
            'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'key' => 'itp',
        ]);
    }

    public function test_checklist_is_ordered_and_annotated(): void
    {
        $contract = $this->makeVehicleContract();
        $checklist = app(DocumentGuidanceService::class)->checklist($contract);

        $orders = $checklist->map(fn ($i) => $i['requirement']->order)->values();
        $this->assertEquals($orders->sort()->values(), $orders);

        $this->assertTrue($checklist->first()['requirement']->key === 'contrato_firmado');
        $this->assertFalse($checklist->first()['uploaded']);
    }

    public function test_completeness_goes_up_after_upload(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeVehicleContract($user->id);
        $guidance = app(DocumentGuidanceService::class);

        $before = $guidance->completeness($contract);
        $this->assertSame(0, $before['done']);

        $file = UploadedFile::fake()->create('dni.pdf', 100, 'application/pdf');
        $this->actingAs($user)->post(route('contracts.documents.upload', $contract), [
            'requirement_key' => 'dni_partes',
            'document' => $file,
        ])->assertRedirect();

        $after = $guidance->completeness($contract->fresh());
        $this->assertSame(1, $after['done']);
        $this->assertTrue($after['done'] < $after['total']);
    }

    public function test_contract_can_be_marked_complete_when_all_uploaded(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = $this->makeVehicleContract($user->id);
        $guidance = app(DocumentGuidanceService::class);

        foreach ($guidance->checklist($contract) as $item) {
            $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
            $this->actingAs($user)->post(route('contracts.documents.upload', $contract), [
                'requirement_key' => $item['requirement']->key,
                'document' => $file,
            ])->assertRedirect();
        }

        $completeness = $guidance->completeness($contract->fresh());
        $this->assertSame($completeness['total'], $completeness['done']);
        $this->assertTrue($completeness['complete']);
    }
}
