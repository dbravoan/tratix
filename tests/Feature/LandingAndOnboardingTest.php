<?php

namespace Tests\Feature;

use App\Mail\MonthlySummaryMail;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
    }

    public function test_landing_page_is_public(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Tratix');
    }

    public function test_privacy_page_is_public(): void
    {
        $this->get(route('privacy'))->assertOk();
    }

    public function test_dashboard_filters_by_status_and_type(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Contract::factory()->create(['user_id' => $user->id, 'status' => 'firmado', 'contract_type' => 'vehiculos', 'title' => 'Coche A']);
        Contract::factory()->create(['user_id' => $user->id, 'status' => 'borrador', 'contract_type' => 'alquiler', 'title' => 'Piso B']);

        $this->actingAs($user)->get(route('dashboard', ['status' => 'firmado']))
            ->assertOk()->assertSee('Coche A')->assertDontSee('Piso B');

        $this->actingAs($user)->get(route('dashboard', ['type' => 'alquiler']))
            ->assertOk()->assertSee('Piso B')->assertDontSee('Coche A');

        $this->actingAs($user)->get(route('dashboard', ['search' => 'Coche']))
            ->assertOk()->assertSee('Coche A')->assertDontSee('Piso B');
    }

    public function test_monthly_summary_command_sends_email(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'created_at' => now()->subDays(10)]);
        Contract::factory()->create(['user_id' => $user->id, 'status' => 'firmado']);

        $this->artisan('contracts:monthly-summary')->assertExitCode(0);

        Mail::assertQueued(MonthlySummaryMail::class, 1);
        Mail::assertQueued(MonthlySummaryMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_referrals_page_requires_auth(): void
    {
        $this->get(route('referrals.index'))->assertRedirect(route('login'));

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('referrals.index'))->assertOk()->assertSee('Referir y ganar');
    }
}
