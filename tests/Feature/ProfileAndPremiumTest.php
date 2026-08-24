<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndPremiumTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'David Desarrollador',
            'email' => 'david@example.com',
            'plan' => 'pro',
            'tax_id' => '12345678Z',
            'party_type' => 'particular',
            'phone' => '+34600000000',
            'address' => 'Paseo de la Castellana 100',
            'postal_code' => '28046',
            'city' => 'Madrid',
            'country' => 'ES',
        ]);
    }

    public function test_user_can_view_profile_and_premium_center(): void
    {
        $res = $this->actingAs($this->user)->get(route('profile.edit'));
        $res->assertOk();
        $res->assertSee('Mi Perfil y Centro Premium');
        $res->assertSee('David Desarrollador');
        $res->assertSee('PRO MEMBER');
        $res->assertSee('Paseo de la Castellana 100');
        $res->assertSee('12345678Z');
    }

    public function test_user_can_update_fiscal_identity_and_preferences(): void
    {
        $res = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'David Pro Edition',
            'email' => 'david.pro@example.com',
            'tax_id' => '99887766K',
            'company_name' => 'Tratix Pro Tech S.L.',
            'party_type' => 'empresa',
            'phone' => '+34611223344',
            'address' => 'Gran Via 28, Planta 5',
            'postal_code' => '28013',
            'city' => 'Madrid',
            'country' => 'ES',
            'notify_comments' => '1',
            'notify_proposals' => '1',
            'notify_signatures' => '0',
            'notify_summary' => '1',
        ]);

        $res->assertRedirect(route('profile.edit'));

        $this->user->refresh();
        $this->assertEquals('David Pro Edition', $this->user->name);
        $this->assertEquals('david.pro@example.com', $this->user->email);
        $this->assertEquals('99887766K', $this->user->tax_id);
        $this->assertEquals('Tratix Pro Tech S.L.', $this->user->company_name);
        $this->assertEquals('empresa', $this->user->party_type);
        $this->assertEquals('+34611223344', $this->user->phone);
        $this->assertEquals('Gran Via 28, Planta 5', $this->user->address);
        $this->assertTrue($this->user->notify_comments);
        $this->assertFalse($this->user->notify_signatures);
    }

    public function test_user_can_switch_plan_in_demo_mode(): void
    {
        $res = $this->actingAs($this->user)->post(route('profile.demo-plan'), [
            'plan' => 'business',
        ]);

        $res->assertRedirect();
        $this->user->refresh();
        $this->assertEquals('business', $this->user->plan);
        $this->assertTrue($this->user->isBusiness());
    }

    public function test_contract_creation_uses_saved_profile_defaults(): void
    {
        $res = $this->actingAs($this->user)->get(route('contracts.create'));
        $res->assertOk();
        $res->assertSee('David Desarrollador');
        $res->assertSee('12345678Z');
        $res->assertSee('Paseo de la Castellana 100');
        $res->assertSee('28046');
    }

    public function test_profile_completion_percentage_and_dashboard_meter(): void
    {
        $newUser = User::factory()->create([
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'email_verified_at' => now(),
            'tax_id' => null,
            'address' => null,
            'city' => null,
            'phone' => null,
        ]);

        // Name + Email verified = 2/5 items done = 40%
        $this->assertEquals(40, $newUser->profileCompletionPercentage());
        $this->assertFalse($newUser->isProfileComplete());

        $res = $this->actingAs($newUser)->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('Completa tu perfil para autocompletar tus contratos');
        $res->assertSee('40% completado');

        // Complete user profile
        $newUser->update([
            'tax_id' => '11223344A',
            'address' => 'Calle Gran Vía 1',
            'city' => 'Madrid',
            'phone' => '+34600112233',
        ]);

        $this->assertEquals(100, $newUser->profileCompletionPercentage());
        $this->assertTrue($newUser->isProfileComplete());

        $resComplete = $this->actingAs($newUser)->get(route('dashboard'));
        $resComplete->assertOk();
        $resComplete->assertDontSee('Completa tu perfil para autocompletar tus contratos');
    }

    public function test_welcome_banner_displayed_on_profile_welcome(): void
    {
        $res = $this->actingAs($this->user)->get(route('profile.edit', ['welcome' => 1]));
        $res->assertOk();
        $res->assertSee('¡Bienvenido a Tratix, David Desarrollador!');
        $res->assertSee('Completar Datos Fiscales');
    }
}
