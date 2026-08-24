<?php

namespace Tests\Unit;

use App\Services\CountryLegalConfig;
use App\Services\LatinAmericanTaxIdValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LatinAmericanTaxIdValidatorTest extends TestCase
{
    private LatinAmericanTaxIdValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LatinAmericanTaxIdValidator;
    }

    public static function validCases(): array
    {
        return [
            'argentina cuit' => ['AR', '20-12345678-6', true],
            'argentina cuit wrong check' => ['AR', '20-12345678-9', false],
            'chile rut' => ['CL', '11.111.111-1', true],
            'chile rut computed' => ['CL', '13.726.886-8', true],
            'chile rut wrong check' => ['CL', '13.726.886-5', false],
            'mexico rfc fisica' => ['MX', 'GARC800101HDF', true],
            'mexico rfc moral' => ['MX', 'ABC850101A12', true],
            'mexico rfc bad' => ['MX', 'ABC12', false],
            'colombia nit' => ['CO', '900123456', true],
            'colombia cedula' => ['CO', '1020456789', true],
            'peru ruc' => ['PE', '20123456789', true],
            'peru dni' => ['PE', '12345678', true],
            'uruguay rut' => ['UY', '123456789012', true],
            'uruguay cedula' => ['UY', '12345678', true],
            'unsupported country' => ['US', '12345', false],
        ];
    }

    #[DataProvider('validCases')]
    public function test_country_tax_ids(string $country, string $taxId, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isValid($country, $taxId));
    }

    public function test_all_supported_countries_have_config(): void
    {
        $config = new CountryLegalConfig;

        foreach (CountryLegalConfig::COUNTRIES as $country) {
            $cfg = $config->config($country);
            $this->assertNotEmpty($cfg['name']);
            $this->assertNotEmpty($cfg['tax_name']);
            $this->assertNotEmpty($cfg['consumer_law']);
            $this->assertNotEmpty($cfg['civil_code']);
            $this->assertNotEmpty($cfg['jurisdiction_clause']);
        }
    }
}
