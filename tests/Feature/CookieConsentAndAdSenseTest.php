<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentAndAdSenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_services_configuration_is_present(): void
    {
        $this->assertNotEmpty(config('services.google.analytics_id'));
        $this->assertNotEmpty(config('services.google.adsense_client'));
    }

    public function test_landing_page_renders_cookie_consent_and_adsense_slots(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Control de Privacidad y Cookies');
        $response->assertSee('Google Analytics 4');
        $response->assertSee('Google AdSense');
        $response->assertSee('Centro de Preferencias de Cookies');
        $response->assertSee('Espacio Patrocinado Google AdSense');
        $response->assertSee('Configuración de cookies');
    }

    public function test_privacy_page_renders_comprehensive_cookie_policy(): void
    {
        $response = $this->get(route('privacy'));

        $response->assertOk();
        $response->assertSee('Política de Cookies y Tecnologías de Terceros');
        $response->assertSee('Cookies Técnicas y de Seguridad (Obligatorias)');
        $response->assertSee('Google Analytics 4');
        $response->assertSee('Google AdSense');
        $response->assertSee('Google Ireland Limited');
        $response->assertSee('Control de Privacidad y Cookies');
    }

    public function test_public_verify_page_renders_cookie_consent_and_adsense(): void
    {
        $response = $this->get('/verify/NONEXISTENT123');

        $response->assertOk();
        $response->assertSee('Control de Privacidad y Cookies');
        $response->assertSee('Espacio Patrocinado Google AdSense');
    }

    public function test_public_review_page_renders_cookie_consent(): void
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->create([
            'user_id' => $user->id,
            'access_token' => 'test-review-token-12345',
            'access_token_expires_at' => now()->addDays(7),
            'status' => 'borrador',
        ]);

        $contract->parties()->create([
            'role' => 'vendedor',
            'party_type' => 'particular',
            'full_name' => 'Vendedor Test',
            'tax_id' => '12345678Z',
            'email' => 'vendedor@test.com',
            'address' => 'Calle Test 1',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'country' => 'ES',
        ]);

        $contract->parties()->create([
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Comprador Test',
            'tax_id' => '87654321X',
            'email' => 'comprador@test.com',
            'address' => 'Calle Test 2',
            'city' => 'Madrid',
            'postal_code' => '28002',
            'country' => 'ES',
        ]);

        $response = $this->get(route('review.show', ['token' => 'test-review-token-12345']));

        $response->assertOk();
        $response->assertSee('Control de Privacidad y Cookies');
        $response->assertSee('Google Analytics 4');
    }
}
