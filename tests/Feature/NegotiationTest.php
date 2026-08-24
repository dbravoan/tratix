<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Services\ContractService;
use App\Services\ContractWorkflowService;
use App\Services\NegotiationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegotiationTest extends TestCase
{
    use RefreshDatabase;

    private function makeContract(): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'title' => 'Compraventa',
            'object_type' => 'Mueble',
            'object_description' => 'Mesa de roble.',
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

    public function test_propose_change_creates_pending_proposal(): void
    {
        $contract = $this->makeContract();
        $negotiation = app(NegotiationService::class);

        $clauseKey = $contract->clauses[0]['key'];
        $original = $contract->clauses[0]['body'];

        $proposal = $negotiation->propose($contract, $clauseKey, 'Nuevo texto de la cláusula.', 'buyer', 'Lo pido así.');

        $this->assertSame('pending', $proposal->status);
        $this->assertSame($original, $proposal->original_text);
        $this->assertSame('buyer', $proposal->proposed_by);
    }

    public function test_approve_applies_proposed_text(): void
    {
        $contract = $this->makeContract();
        $negotiation = app(NegotiationService::class);
        $clauseKey = $contract->clauses[0]['key'];

        $proposal = $negotiation->propose($contract, $clauseKey, 'Nuevo texto aprobado.', 'buyer');
        $negotiation->approve($contract, $proposal, 'creator');

        $contract->refresh();
        $updated = collect($contract->clauses)->firstWhere('key', $clauseKey);

        $this->assertSame('approved', $proposal->fresh()->status);
        $this->assertSame('Nuevo texto aprobado.', $updated['body']);
    }

    public function test_reject_keeps_original_text(): void
    {
        $contract = $this->makeContract();
        $negotiation = app(NegotiationService::class);
        $clauseKey = $contract->clauses[0]['key'];
        $original = $contract->clauses[0]['body'];

        $proposal = $negotiation->propose($contract, $clauseKey, 'Texto rechazado.', 'buyer');
        $negotiation->reject($contract, $proposal, 'creator');

        $contract->refresh();
        $updated = collect($contract->clauses)->firstWhere('key', $clauseKey);

        $this->assertSame('rejected', $proposal->fresh()->status);
        $this->assertSame($original, $updated['body']);
    }

    public function test_cannot_propose_on_frozen_contract(): void
    {
        $contract = $this->makeContract();
        $negotiation = app(NegotiationService::class);

        app(ContractWorkflowService::class)->transition($contract, 'lista_para_firma', 'creator');

        $this->expectException(\DomainException::class);
        $negotiation->propose($contract, $contract->clauses[0]['key'], 'texto', 'buyer');
    }

    public function test_unknown_clause_key_throws(): void
    {
        $contract = $this->makeContract();

        $this->expectException(\DomainException::class);
        app(NegotiationService::class)->propose($contract, 'no_existe', 'texto', 'buyer');
    }
}
