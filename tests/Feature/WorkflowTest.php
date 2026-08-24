<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeContract(): Contract
    {
        $service = app(ContractService::class);

        return $service->create([
            'contract_type' => 'vehiculos',
            'title' => 'Compraventa de coche',
            'object_type' => 'Vehículo',
            'object_description' => 'Seat Ibiza 2020.',
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
        ]);
    }

    public function test_full_happy_path_through_all_states(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $user = User::factory()->create();
        $contract = $this->makeContract();

        $this->assertSame('borrador', $contract->status);

        $contract = $workflow->transition($contract, 'en_revision', 'creator', $user);
        $this->assertSame('en_revision', $contract->status);

        $contract = $workflow->transition($contract, 'lista_para_firma', 'creator', $user);
        $this->assertSame('lista_para_firma', $contract->status);
        $this->assertNotNull($contract->latestVersion());
        $this->assertNotNull($contract->latestVersion()->frozen_at);
        $this->assertSame(64, strlen($contract->latestVersion()->hash));

        $contract = $workflow->transition($contract, 'en_firma', 'creator', $user);
        $this->assertSame('en_firma', $contract->status);

        $contract = $workflow->transition($contract, 'firmado', 'system');
        $this->assertSame('firmado', $contract->status);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $contract = $this->makeContract();

        $this->expectException(\DomainException::class);
        $workflow->transition($contract, 'firmado', 'system');
    }

    public function test_cancel_is_always_possible_before_signing(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $contract = $this->makeContract();

        $contract = $workflow->transition($contract, 'cancelado', 'creator');
        $this->assertSame('cancelado', $contract->status);

        $this->assertEmpty($workflow->allowedNextStates($contract));
    }

    public function test_every_transition_is_audited(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $user = User::factory()->create();
        $contract = $this->makeContract();

        $contract = $workflow->transition($contract, 'en_revision', 'creator', $user);

        $this->assertDatabaseHas('audit_trail', [
            'contract_id' => $contract->id,
            'event' => 'sent_for_review',
            'actor' => 'creator',
        ]);
    }

    public function test_freeze_generates_deterministic_hash(): void
    {
        $workflow = app(ContractWorkflowService::class);
        $a = $this->makeContract();
        $b = $this->makeContract();

        $workflow->transition($a, 'lista_para_firma', 'creator');
        $workflow->transition($b, 'lista_para_firma', 'creator');

        $this->assertSame(
            $a->latestVersion()->hash,
            $b->latestVersion()->hash,
            'Igual contenido debe producir el mismo hash.'
        );
    }
}
