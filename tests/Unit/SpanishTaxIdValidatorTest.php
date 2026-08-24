<?php

namespace Tests\Unit;

use App\Services\SpanishTaxIdValidator;
use PHPUnit\Framework\TestCase;

class SpanishTaxIdValidatorTest extends TestCase
{
    private SpanishTaxIdValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SpanishTaxIdValidator;
    }

    public function test_valid_dni(): void
    {
        $this->assertTrue($this->validator->isValid('12345678Z'));
        $this->assertTrue($this->validator->isValidNif('12345678Z'));
    }

    public function test_valid_dni_with_normalization(): void
    {
        $this->assertTrue($this->validator->isValid(' 12.345.678-Z '));
        $this->assertTrue($this->validator->isValid('12345678z'));
    }

    public function test_invalid_dni(): void
    {
        $this->assertFalse($this->validator->isValid('12345678A'));
        $this->assertFalse($this->validator->isValid('1234567Z'));
        $this->assertFalse($this->validator->isValid('123456789'));
    }

    public function test_valid_nie(): void
    {
        $this->assertTrue($this->validator->isValid('X0000000T'));
        $this->assertTrue($this->validator->isValidNie('X0000000T'));
    }

    public function test_invalid_nie(): void
    {
        $this->assertFalse($this->validator->isValid('X0000000A'));
        $this->assertFalse($this->validator->isValid('Z12345678'));
    }

    public function test_valid_cif_numeric_control(): void
    {
        $this->assertTrue($this->validator->isValid('B12345679'));
        $this->assertTrue($this->validator->isValidCif('B12345679'));
    }

    public function test_valid_cif_letter_control(): void
    {
        $this->assertTrue($this->validator->isValid('B1234567I'));
    }

    public function test_invalid_cif(): void
    {
        $this->assertFalse($this->validator->isValid('B12345678'));
        $this->assertFalse($this->validator->isValid('K12345679'));
    }
}
