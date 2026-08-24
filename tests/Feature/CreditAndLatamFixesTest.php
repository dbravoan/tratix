<?php

namespace Tests\Feature;

use App\Mail\ReviewInviteMail;
use App\Models\Contract;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreditAndLatamFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_plan_has_unlimited_contracts(): void
    {
        $user = User::factory()->create(['plan' => 'business']);
        $service = app(CreditService::class);

        $this->assertNull($service->monthlyLimit($user));
        $this->assertTrue($service->canCreate($user));
        $this->assertSame(PHP_INT_MAX, $service->remaining($user));
    }

    public function test_purchased_credits_allow_creation_beyond_monthly_limit(): void
    {
        $user = User::factory()->create([
            'plan' => 'free',
            'credits' => 2,
            'email_verified_at' => now(),
        ]);
        $service = app(CreditService::class);

        // Exhaust the 3 free contracts
        for ($i = 0; $i < 3; $i++) {
            Contract::factory()->create([
                'user_id' => $user->id,
                'status' => 'borrador',
                'created_at' => now(),
            ]);
        }

        $this->assertSame(3, $service->usedThisMonth($user));
        $this->assertSame(2, $service->remaining($user));
        $this->assertTrue($service->canCreate($user));

        // Create one more contract via HTTP
        $payload = [
            'contract_type' => 'bienes_muebles',
            'creator_role' => 'vendedor',
            'title' => 'Contrato con créditos',
            'object_type' => 'Mueble',
            'object_description' => 'Mesa',
            'quantity' => 1,
            'price_amount' => 100,
            'tax_amount' => 0,
            'currency' => 'EUR',
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'seller' => [
                'party_type' => 'particular',
                'full_name' => 'Ana López',
                'tax_id' => '12345678Z',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Gran Via 1',
                'postal_code' => '28013',
                'city' => 'Madrid',
            ],
            'buyer' => [
                'party_type' => 'particular',
                'full_name' => 'Pedro Soler',
                'tax_id' => '87654321X',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Gran Via 2',
                'postal_code' => '28013',
                'city' => 'Madrid',
            ],
        ];

        $response = $this->actingAs($user)->post(route('contracts.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertSame(1, $user->credits);
    }

    public function test_ajax_check_tax_id_validates_latam_tax_ids(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Argentina CUIT valid
        $res = $this->actingAs($user)->json('GET', route('contracts.tax-id-check'), [
            'country' => 'AR',
            'tax_id' => '20-12345678-6',
        ]);
        $res->assertOk()->assertJson([
            'valid' => true,
            'type' => 'tax-ar',
        ]);

        // Chile RUT valid
        $res = $this->actingAs($user)->json('GET', route('contracts.tax-id-check'), [
            'country' => 'CL',
            'tax_id' => '11.111.111-1',
        ]);
        $res->assertOk()->assertJson([
            'valid' => true,
            'type' => 'tax-cl',
        ]);

        // Spain NIF valid
        $res = $this->actingAs($user)->json('GET', route('contracts.tax-id-check'), [
            'country' => 'ES',
            'tax_id' => '12345678Z',
        ]);
        $res->assertOk()->assertJson([
            'valid' => true,
            'type' => 'nif',
        ]);
    }

    public function test_send_review_generates_valid_access_token_in_invite_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $contract = Contract::factory()->create([
            'user_id' => $user->id,
            'status' => 'borrador',
            'creator_role' => 'vendedor',
            'access_token' => null,
        ]);

        $this->actingAs($user)->post(route('contracts.send-review', $contract), [
            'invited_email' => 'contraparte@ejemplo.com',
        ])->assertRedirect();

        $contract->refresh();
        $this->assertNotNull($contract->access_token);
        $this->assertSame('en_revision', $contract->status);

        Mail::assertQueued(ReviewInviteMail::class, function (ReviewInviteMail $mail) use ($contract) {
            return $mail->hasTo('contraparte@ejemplo.com')
                && str_contains($mail->reviewUrl, $contract->access_token);
        });
    }
}
