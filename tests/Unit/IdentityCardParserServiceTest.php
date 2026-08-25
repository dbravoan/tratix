<?php

namespace Tests\Unit;

use App\Services\CountryLegalConfig;
use App\Services\IdentityCardParserService;
use App\Services\LatinAmericanTaxIdValidator;
use App\Services\SpanishTaxIdValidator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class IdentityCardParserServiceTest extends TestCase
{
    private IdentityCardParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $spanishValidator = new SpanishTaxIdValidator();
        $latamValidator = new LatinAmericanTaxIdValidator(new CountryLegalConfig());
        $this->parser = new IdentityCardParserService($spanishValidator, $latamValidator);
    }

    public function test_parses_spanish_dni_anverso(): void
    {
        $text = "ESPAÑA\nDOCUMENTO NACIONAL DE IDENTIDAD\n1. APELLIDO / 1st SURNAME\nGARCIA\n2. APELLIDO / 2nd SURNAME\nLOPEZ\nNOMBRE / GIVEN NAME\nJUAN CARLOS\nSEXO M NACIONALIDAD ESP\nNUM 12.345.678-Z\nVALIDEZ 15 05 2030";
        $result = $this->parser->parseText($text);

        $this->assertTrue($result['success']);
        $this->assertEquals('12345678Z', $result['tax_id']);
        $this->assertEquals('ES', $result['tax_id_country']);
        $this->assertTrue($result['tax_id_valid']);
        $this->assertEquals('Juan Carlos Garcia Lopez', $result['full_name']);
        $this->assertEquals('Juan Carlos', $result['first_name']);
        $this->assertEquals('Garcia Lopez', $result['last_name']);
    }

    public function test_parses_spanish_dni_3_mrz_with_support_number_and_real_nif_in_line_2(): void
    {
        // Real Spanish DNI 3.0 MRZ where line 1 has support CKL159690 and line 2 has real NIF 12345678Z
        $mrz = "DOMICILIO: CALLE ALCALA 45 2ºB\nMUNICIPIO: MADRID\nCP: 28014\nIDESPCKL159690<<<<<<<<<<<<<<<\n8505152M3005154ESP12345678Z<<<<8\nGARCIA<LOPEZ<<JUAN<CARLOS<<<<<";
        $result = $this->parser->parseText($mrz);

        $this->assertTrue($result['success']);
        $this->assertEquals('12345678Z', $result['tax_id'], 'Must extract real NIF from line 2 instead of support number');
        $this->assertEquals('CKL159690', $result['support_number'], 'Must store CKL159690 as support_number');
        $this->assertEquals('ES', $result['tax_id_country']);
        $this->assertTrue($result['tax_id_valid']);
        $this->assertEquals('Juan Carlos Garcia Lopez', $result['full_name']);
        $this->assertEquals('CALLE ALCALA 45 2ºB', $result['address']);
        $this->assertEquals('28014', $result['postal_code']);
        $this->assertEquals('Madrid', $result['city']);
    }

    public function test_parses_nie_foreign_identity_card(): void
    {
        $text = "REINO DE ESPAÑA\nTARJETA DE IDENTIDAD DE EXTRANJERO\nNIE: X-1234567-L\n1er APELLIDO: SMITH\n2º APELLIDO: BROWN\nNOMBRE: JOHN";
        $result = $this->parser->parseText($text);

        $this->assertTrue($result['success']);
        $this->assertEquals('X1234567L', $result['tax_id']);
        $this->assertEquals('ES', $result['tax_id_country']);
        $this->assertTrue($result['tax_id_valid']);
        $this->assertEquals('nie', $result['tax_id_kind']);
        $this->assertEquals('John Smith Brown', $result['full_name']);
    }

    public function test_parses_complete_address_from_dni_reverso(): void
    {
        $text = "DOMICILIO: PASEO DE LA CASTELLANA 100 4ºA\nMUNICIPIO: MADRID\nPROVINCIA: MADRID\nCP: 28046\nPAÍS DE DOMICILIO: ESPAÑA\nIDESP12345678Z0<<<<<<<<<<<<<<<\n8801014M3210108ESP<<<<<<<<<<<2\nRODRIGUEZ<SANCHEZ<<DAVID<<<<<<";
        $result = $this->parser->parseText($text);

        $this->assertTrue($result['success']);
        $this->assertEquals('PASEO DE LA CASTELLANA 100 4ºA', $result['address']);
        $this->assertEquals('28046', $result['postal_code']);
        $this->assertEquals('Madrid', $result['city']);
        $this->assertEquals('Madrid', $result['province']);
    }

    public function test_extract_raw_text_does_not_convert_binary_photo_noise_into_garbage(): void
    {
        $fakeBinaryImage = UploadedFile::fake()->createWithContent('photo.jpg', "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00\xFF\xDB\x00C\x00Yhne0gpctfPxxwaw9x6l5xngls4truscx7dzm4xpysxf4vbmt9ctey8gcj4xgk7pvbdohykj5z7kbxcl7ayfe");

        $rawText = $this->parser->extractRawText($fakeBinaryImage);
        $this->assertEmpty($rawText, 'Must not return decoded binary garbage from images');

        $result = $this->parser->parseText($rawText);
        $this->assertFalse($result['success']);
        $this->assertNull($result['full_name']);
        $this->assertNull($result['tax_id']);
    }

    public function test_is_valid_human_name_rejects_garbage_entropy(): void
    {
        $this->assertTrue($this->parser->isValidHumanName('Juan Carlos García López'));
        $this->assertTrue($this->parser->isValidHumanName('María del Carmen Ruiz-Hernández'));
        $this->assertTrue($this->parser->isValidHumanName('Jean-Pierre Dupont'));

        // Reject garbage binary strings
        $this->assertFalse($this->parser->isValidHumanName('Yhne0gpctf Pxxwaw9x6l5xngls4truscx7dzm4xpysxf4vbmt9ctey8gcj4xgk7pvbdohykj5z7kbxcl7ayfe O'));
        $this->assertFalse($this->parser->isValidHumanName('12345678Z'));
        $this->assertFalse($this->parser->isValidHumanName('bcdfghjklmnpqrstvwxyz'));
        $this->assertFalse($this->parser->isValidHumanName(null));
    }

    public function test_parses_argentina_cuit_and_address(): void
    {
        $text = "REPÚBLICA ARGENTINA\nREGISTRO NACIONAL DE LAS PERSONAS\nDOCUMENTO NACIONAL DE IDENTIDAD\nAPELLIDO: GONZALEZ\nNOMBRE: MARTIN\nCUIT: 20-30123456-3\nDOMICILIO: AV CORRIENTES 1234\nLOCALIDAD: BUENOS AIRES\nCP: 1425";
        $result = $this->parser->parseText($text);

        $this->assertTrue($result['success']);
        $this->assertEquals('20-30123456-3', $result['tax_id']);
        $this->assertEquals('AR', $result['tax_id_country']);
        $this->assertEquals('Martin Gonzalez', $result['full_name']);
        $this->assertEquals('AV CORRIENTES 1234', $result['address']);
        $this->assertEquals('1425', $result['postal_code']);
    }

    public function test_parses_mexico_ine_and_rfc(): void
    {
        $text = "INSTITUTO NACIONAL ELECTORAL\nCREDENCIAL PARA VOTAR\nNOMBRE: LOPEZ HERNANDEZ SOFIA\nDOMICILIO: CALLE INSURGENTES SUR 1200\nCOLONIA DEL VALLE\nC.P. 03100\nALCALDÍA: BENITO JUAREZ\nESTADO: CIUDAD DE MEXICO\nRFC: LOHS8505151A0";
        $result = $this->parser->parseText($text);

        $this->assertTrue($result['success']);
        $this->assertEquals('LOHS8505151A0', $result['tax_id']);
        $this->assertEquals('MX', $result['tax_id_country']);
        $this->assertEquals('03100', $result['postal_code']);
    }
}
