<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function createContract(User $user, string $title = 'Contrato'): Contract
    {
        return app(ContractService::class)->create([
            'contract_type' => 'bienes_muebles',
            'creator_role' => 'vendedor',
            'title' => $title,
            'object_type' => 'Mueble',
            'object_description' => 'Mesa de madera.',
            'quantity' => 1,
            'price_amount' => 500,
            'currency' => 'EUR',
            'tax_amount' => 0,
            'city' => 'Valencia',
            'signing_date' => now()->toDateString(),
        ], [
            'role' => 'vendedor', 'party_type' => 'particular', 'full_name' => 'Vendedor 1',
            'tax_id' => '12345678Z', 'tax_id_country' => 'ES', 'country' => 'ES',
            'email' => 'v1@ejemplo.com', 'address' => 'Calle 1', 'postal_code' => '46001', 'city' => 'Valencia',
        ], [
            'role' => 'comprador', 'party_type' => 'particular', 'full_name' => 'Comprador 1',
            'tax_id' => '87654321X', 'tax_id_country' => 'ES', 'country' => 'ES',
            'email' => 'c1@ejemplo.com', 'address' => 'Calle 2', 'postal_code' => '46002', 'city' => 'Valencia',
        ], $user->id);
    }

    public function test_document_download_prevents_cross_contract_bola_idor(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract1 = $this->createContract($user, 'Contrato 1');
        $contract2 = $this->createContract($user, 'Contrato 2');

        $file = UploadedFile::fake()->create('contract2_secret.pdf', 100, 'application/pdf');
        $path = $file->store('documents/'.$contract2->reference, 'local');

        $doc2 = ContractDocument::create([
            'contract_id' => $contract2->id,
            'requirement_key' => 'doc_propiedad',
            'filename' => 'contract2_secret.pdf',
            'path' => $path,
            'mime' => 'application/pdf',
            'size' => 1024,
            'status' => 'uploaded',
            'uploaded_by_user_id' => $user->id,
            'uploaded_at' => now(),
        ]);

        // Attempting to access contract2's document using contract1's URL route must return 404
        $this->actingAs($user)
            ->get(route('contracts.documents.download', ['contract' => $contract1, 'document' => $doc2]))
            ->assertNotFound();

        // Valid scoped access on contract2 returns download
        $this->actingAs($user)
            ->get(route('contracts.documents.download', ['contract' => $contract2, 'document' => $doc2]))
            ->assertOk();
    }

    public function test_stripe_webhook_rejects_missing_secret(): void
    {
        config(['billing.gateway' => 'stripe']);
        config(['billing.stripe.webhook_secret' => null]);

        $response = $this->postJson(route('billing.webhook'), [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => ['user_id' => 1, 'plan' => 'pro'],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => 'Stripe webhook secret is not configured.']);
    }

    public function test_stripe_webhook_rejects_missing_signature(): void
    {
        config(['billing.gateway' => 'stripe']);
        config(['billing.stripe.webhook_secret' => 'whsec_test_12345']);

        $response = $this->postJson(route('billing.webhook'), [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => ['user_id' => 1, 'plan' => 'pro'],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => 'Missing Stripe-Signature header.']);
    }
}
