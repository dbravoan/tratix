<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_seo_and_value_propositions(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Tratix · Contratos de Compraventa Legales');
        $response->assertSee('Tratix vs PDF');
        $response->assertSee('¿Por qué rellenar un PDF descargado es una trampa legal?');
        $response->assertSee('Escáner OCR Dual Anverso + Reverso');
        $response->assertSee('Delegación a la Contraparte sin Registro');
        $response->assertSee('Firma Electrónica eIDAS Certificada');
        $response->assertSee('Precios Claros y Sin Sorpresas');
        $response->assertSee('Sobre Nosotros');
        $response->assertSee('Atención Personalizada');
        $response->assertSee('SoftwareApplication');
        $response->assertSee('FAQPage');
    }

    public function test_contact_form_validates_required_fields_and_gdpr(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => 'short',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'gdpr_consent']);
    }

    public function test_contact_form_submits_successfully_with_valid_data(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Carlos Mendoza',
            'email' => 'carlos@ejemplo.com',
            'subject' => 'Consulta sobre contrato de compraventa B2B',
            'message' => 'Hola, me gustaría saber si el contrato de prestación de servicios incluye cláusulas de confidencialidad y cesión de derechos.',
            'gdpr_consent' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_contact_form_ajax_json_submission(): void
    {
        $response = $this->postJson(route('contact.submit'), [
            'name' => 'Laura Gomez',
            'email' => 'laura@ejemplo.com',
            'subject' => 'Duda sobre firma electrónica eIDAS',
            'message' => '¿Tiene validez jurídica un contrato firmado desde fuera de España con código OTP?',
            'gdpr_consent' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('Gracias por contactar con Tratix', $response->json('message'));
    }

    public function test_contact_form_rejects_bot_with_honeypot(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Bot Spammer',
            'email' => 'bot@spammer.com',
            'subject' => 'Buy cheap links',
            'message' => 'This is a spam message filled by a malicious bot.',
            'gdpr_consent' => '1',
            'website_hp' => 'http://spam-link.com',
        ]);

        $response->assertSessionHasErrors(['website_hp']);
    }
}
