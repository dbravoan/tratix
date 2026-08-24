<?php

namespace Tests\Unit;

use App\Services\EuVatValidator;
use App\Services\SpanishTaxIdValidator;
use PHPUnit\Framework\TestCase;

class EuVatValidatorTest extends TestCase
{
    private EuVatValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new EuVatValidator(new SpanishTaxIdValidator);
    }

    public function test_country_membership(): void
    {
        $this->assertTrue($this->validator->isValidCountry('ES'));
        $this->assertTrue($this->validator->isValidCountry('DE'));
        $this->assertFalse($this->validator->isValidCountry('US'));
        $this->assertFalse($this->validator->isValidCountry('GB'));
    }

    public function test_spanish_vat_uses_nif_cif_logic(): void
    {
        $this->assertTrue($this->validator->hasValidFormat('ES', 'B12345679'));
        $this->assertTrue($this->validator->hasValidFormat('ES', '12345678Z'));
        $this->assertFalse($this->validator->hasValidFormat('ES', 'B12345678'));
    }

    public function test_german_vat_format(): void
    {
        $this->assertTrue($this->validator->hasValidFormat('DE', '123456789'));
        $this->assertFalse($this->validator->hasValidFormat('DE', '12345678'));
    }

    public function test_french_vat_format(): void
    {
        $this->assertTrue($this->validator->hasValidFormat('FR', 'AB123456789'));
        $this->assertFalse($this->validator->hasValidFormat('FR', '1234567890'));
    }

    public function test_strips_country_prefix(): void
    {
        $this->assertTrue($this->validator->hasValidFormat('DE', 'DE123456789'));
        $this->assertTrue($this->validator->hasValidFormat('IT', 'IT12345678901'));
    }

    public function test_normalizes_vat(): void
    {
        $this->assertTrue($this->validator->hasValidFormat('FR', 'fr ab123456789'));
    }

    public function test_format_only_validation_result(): void
    {
        $result = $this->validator->validate('DE', '123456789', vies: false);
        $this->assertTrue($result['valid']);
        $this->assertFalse($result['checked_via_vies']);
    }

    public function test_validate_rejects_bad_format_without_vies_call(): void
    {
        $result = $this->validator->validate('DE', '123', vies: true);
        $this->assertFalse($result['valid']);
        $this->assertFalse($result['checked_via_vies']);
    }
}
