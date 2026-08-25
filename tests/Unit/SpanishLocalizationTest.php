<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class SpanishLocalizationTest extends TestCase
{
    public function test_default_application_locale_is_spanish(): void
    {
        $this->assertEquals('es', config('app.locale'));
        $this->assertEquals('es', config('app.fallback_locale'));
        $this->assertEquals('es', app()->getLocale());
    }

    public function test_auth_translations_are_in_spanish(): void
    {
        $this->assertEquals('Estas credenciales no coinciden con nuestros registros.', __('auth.failed'));
        $this->assertEquals('La contraseña proporcionada es incorrecta.', __('auth.password'));
    }

    public function test_password_translations_are_in_spanish(): void
    {
        $this->assertEquals('Tu contraseña ha sido restablecida.', __('passwords.reset'));
        $this->assertEquals('Hemos enviado a tu correo el enlace para restablecer tu contraseña.', __('passwords.sent'));
        $this->assertEquals('No encontramos ningún usuario con esa dirección de correo electrónico.', __('passwords.user'));
    }

    public function test_json_translations_for_auth_forms(): void
    {
        $this->assertEquals('Iniciar sesión', __('Log in'));
        $this->assertEquals('Registrarse', __('Register'));
        $this->assertEquals('Correo electrónico', __('Email'));
        $this->assertEquals('Contraseña', __('Password'));
        $this->assertEquals('Confirmar contraseña', __('Confirm Password'));
        $this->assertEquals('Recordarme', __('Remember me'));
        $this->assertEquals('¿Olvidaste tu contraseña?', __('Forgot your password?'));
    }

    public function test_validation_attributes_and_messages_are_in_spanish(): void
    {
        $this->assertEquals('El campo correo electrónico es obligatorio.', __('validation.required', ['attribute' => __('validation.attributes.email')]));
        $this->assertEquals('El campo contraseña es obligatorio.', __('validation.required', ['attribute' => __('validation.attributes.password')]));
    }
}
