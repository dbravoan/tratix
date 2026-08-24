<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReferralAndPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_referral_link_generates_code(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $service = app(ReferralService::class);

        $url = $service->referralUrl($user);
        $this->assertStringContainsString('/ref/', $url);
        $this->assertNotEmpty($user->fresh()->referral_code);
    }

    public function test_referral_route_stores_code_and_redirects_to_register(): void
    {
        $referrer = User::factory()->create(['email_verified_at' => now()]);
        $code = app(ReferralService::class)->generateCode($referrer);

        $this->get(route('referral.show', $code))->assertRedirect(route('register'));
        $this->assertEquals($code, session('referral_code'));
    }

    public function test_referral_grants_credits_to_referred(): void
    {
        $referrer = User::factory()->create(['email_verified_at' => now()]);
        $code = app(ReferralService::class)->generateCode($referrer);

        // Register through the referral link.
        $this->get(route('referral.show', $code));

        $this->post('/register', [
            'name' => 'Nuevo',
            'email' => 'nuevo@ejemplo.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('profile.edit', ['welcome' => 1]));

        $referred = User::where('email', 'nuevo@ejemplo.com')->first();
        $this->assertNotNull($referred);
        $this->assertGreaterThan(0, $referred->credits);
        $this->assertDatabaseHas('referrals', ['referrer_id' => $referrer->id, 'referred_id' => $referred->id]);
    }

    public function test_export_requires_pro_or_business_plan(): void
    {
        $free = User::factory()->create(['email_verified_at' => now(), 'plan' => 'free']);
        $this->actingAs($free)->get(route('contracts.export'))->assertRedirect(route('billing.pricing'));

        // Pro is allowed: with no contracts it redirects to dashboard (not pricing).
        $pro = User::factory()->create(['email_verified_at' => now(), 'plan' => 'pro']);
        $this->actingAs($pro)->get(route('contracts.export'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_checkout_accepts_business_plan(): void
    {
        config(['billing.gateway' => 'demo']);
        $user = User::factory()->create(['email_verified_at' => now(), 'plan' => 'free']);
        $this->actingAs($user);

        $this->post(route('billing.checkout'), ['plan' => 'business'])->assertRedirect();
        $this->assertSame('business', $user->fresh()->plan);
    }
}
