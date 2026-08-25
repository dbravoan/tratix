<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use App\Services\IdentityCardParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdentityCardScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_spanish_dni_anverso_multiline_layout(): void
    {
        $parser = app(IdentityCardParserService::class);

        $ocrText = "ESPAÑA\nDOCUMENTO NACIONAL DE IDENTIDAD\n1. APELLIDO / 1st SURNAME\nGARCIA\n2. APELLIDO / 2nd SURNAME\nLOPEZ\nNOMBRE / GIVEN NAME\nJUAN CARLOS\nSEXO M NACIONALIDAD ESP\nFECHA DE NACIMIENTO 15 05 1985\nNUM 12.345.678-Z\nVALIDEZ 15 05 2030";
        $parsed = $parser->parseText($ocrText);

        $this->assertTrue($parsed['success']);
        $this->assertEquals('front', $parsed['side']);
        $this->assertEquals('12345678Z', $parsed['tax_id']);
        $this->assertEquals('ES', $parsed['tax_id_country']);
        $this->assertTrue($parsed['tax_id_valid']);
        $this->assertEquals('Juan Carlos Garcia Lopez', $parsed['full_name']);
        $this->assertEquals('Juan Carlos', $parsed['first_name']);
        $this->assertEquals('Garcia Lopez', $parsed['last_name']);
        $this->assertEquals('15-05-2030', $parsed['expiry_date']);
    }

    public function test_parses_spanish_dni_mrz_reverso_td1_format(): void
    {
        $parser = app(IdentityCardParserService::class);

        // TD1 standard 3 lines with address
        $mrzText = "DOMICILIO: CALLE ALCALA 45 2ºB\nMUNICIPIO: MADRID\nPROVINCIA: MADRID\nCP: 28014\nIDESP12345678Z0<<<<<<<<<<<<<<<\n8505152M3005154ESP<<<<<<<<<<<8\nGARCIA<LOPEZ<<JUAN<CARLOS<<<<<";
        $parsed = $parser->parseText($mrzText);

        $this->assertTrue($parsed['success']);
        $this->assertEquals('back', $parsed['side']);
        $this->assertEquals('12345678Z', $parsed['tax_id']);
        $this->assertEquals('ES', $parsed['tax_id_country']);
        $this->assertEquals('Juan Carlos Garcia Lopez', $parsed['full_name']);
        $this->assertEquals('CALLE ALCALA 45 2ºB', $parsed['address']);
        $this->assertEquals('28014', $parsed['postal_code']);
        $this->assertEquals('Madrid', $parsed['city']);
        $this->assertEquals('2030-05-15', $parsed['expiry_date']);
    }

    public function test_parses_nie_foreign_identity_card(): void
    {
        $parser = app(IdentityCardParserService::class);

        $ocrText = "REINO DE ESPAÑA\nTARJETA DE IDENTIDAD DE EXTRANJERO\nNIE: X-1234567-L\n1er APELLIDO: SMITH\n2º APELLIDO: BROWN\nNOMBRE: JOHN";
        $parsed = $parser->parseText($ocrText);

        $this->assertTrue($parsed['success']);
        $this->assertEquals('X1234567L', $parsed['tax_id']);
        $this->assertEquals('ES', $parsed['tax_id_country']);
        $this->assertTrue($parsed['tax_id_valid']);
        $this->assertEquals('John Smith Brown', $parsed['full_name']);
    }

    public function test_scan_id_endpoint_supports_front_and_back_with_ocr_text(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // 1. Front scan with client OCR
        $fileFront = UploadedFile::fake()->createWithContent('anverso.jpg', 'binary-photo-data');
        $ocrFront = "ESPAÑA DOCUMENTO NACIONAL DE IDENTIDAD\n1. APELLIDO\nRODRIGUEZ\n2. APELLIDO\nSANCHEZ\nNOMBRE\nDAVID\nNUM: 52.345.678-W\nVALIDEZ 10 10 2032";

        $resFront = $this->actingAs($user)->postJson(route('contracts.scan-id'), [
            'document' => $fileFront,
            'side' => 'front',
            'ocr_text' => $ocrFront,
        ]);

        $resFront->assertOk();
        $resFront->assertJson([
            'success' => true,
            'side' => 'front',
            'tax_id' => '52345678W',
            'full_name' => 'David Rodriguez Sanchez',
        ]);
        $frontToken = $resFront->json('scan_token');
        $this->assertNotEmpty($frontToken);

        // 2. Back scan with client OCR
        $fileBack = UploadedFile::fake()->createWithContent('reverso.jpg', 'binary-photo-data');
        $ocrBack = "DOMICILIO: PASEO DE LA CASTELLANA 100 4ºA\nMUNICIPIO: MADRID\nPROVINCIA: MADRID\nCP: 28046\nIDESP52345678W0<<<<<<<<<<<<<<<\n8801014M3210108ESP<<<<<<<<<<<2\nRODRIGUEZ<SANCHEZ<<DAVID<<<<<<";

        $resBack = $this->actingAs($user)->postJson(route('contracts.scan-id'), [
            'document' => $fileBack,
            'side' => 'back',
            'ocr_text' => $ocrBack,
        ]);

        $resBack->assertOk();
        $resBack->assertJson([
            'success' => true,
            'side' => 'back',
            'address' => 'PASEO DE LA CASTELLANA 100 4ºA',
            'postal_code' => '28046',
            'city' => 'Madrid',
        ]);
        $backToken = $resBack->json('scan_token');
        $this->assertNotEmpty($backToken);
    }

    public function test_contract_creation_attaches_both_anverso_and_reverso_id_cards(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // Upload front
        $fileFront = UploadedFile::fake()->createWithContent('front.png', 'front-content');
        $frontRes = $this->actingAs($user)->postJson(route('contracts.scan-id'), [
            'document' => $fileFront,
            'side' => 'front',
            'ocr_text' => "1. APELLIDO\nNAVARRO\nNOMBRE\nLAURA\nDNI 12345678Z",
        ]);
        $frontToken = $frontRes->json('scan_token');

        // Upload back
        $fileBack = UploadedFile::fake()->createWithContent('back.png', 'back-content');
        $backRes = $this->actingAs($user)->postJson(route('contracts.scan-id'), [
            'document' => $fileBack,
            'side' => 'back',
            'ocr_text' => 'DOMICILIO: CALLE GOYA 20 CP: 28001 MUNICIPIO: MADRID',
        ]);
        $backToken = $backRes->json('scan_token');

        $contractData = [
            'contract_type' => 'inmuebles',
            'title' => 'Venta de Apartamento',
            'object_description' => 'Apartamento en Madrid',
            'quantity' => 1,
            'price_amount' => 150000,
            'currency' => 'EUR',
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'creator_role' => 'vendedor',
            'seller' => [
                'party_type' => 'particular',
                'full_name' => 'Laura Navarro',
                'tax_id' => '12345678Z',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Calle Goya 20',
                'postal_code' => '28001',
                'city' => 'Madrid',
                'id_card_front_token' => $frontToken,
                'id_card_back_token' => $backToken,
            ],
            'buyer' => [
                'party_type' => 'particular',
                'full_name' => 'Pedro Garcia',
                'tax_id' => '71234567A',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'Calle Serrano 10',
                'postal_code' => '28001',
                'city' => 'Madrid',
            ],
        ];

        $res = $this->actingAs($user)->post(route('contracts.store'), $contractData);
        $res->assertRedirect();

        $contract = Contract::latest()->first();
        $this->assertNotNull($contract);

        // Verify both documents exist
        $docs = $contract->documents()->where('requirement_key', 'dni_partes')->get();
        $this->assertCount(2, $docs);
    }

    public function test_contract_creation_for_arras_penitenciales(): void
    {
        $user = User::factory()->create();

        $contractData = [
            'contract_type' => 'arras',
            'title' => 'Contrato de arras penitenciales para compra de vivienda',
            'object_description' => 'Vivienda sita en C/ Mayor 15, 3ºB, Madrid. Finca registral 12345.',
            'quantity' => 1,
            'price_amount' => 200000,
            'currency' => 'EUR',
            'city' => 'Madrid',
            'signing_date' => now()->toDateString(),
            'effective_date' => now()->addMonths(2)->toDateString(),
            'payment_terms' => 'Arras de 20.000 € abonadas mediante transferencia bancaria. Resto (180.000 €) a la firma notarial.',
            'delivery_terms' => 'Escritura pública antes de 60 días en Notaría de Madrid.',
            'creator_role' => 'vendedor',
            'seller' => [
                'party_type' => 'particular',
                'full_name' => 'Vendedor Ejemplo',
                'tax_id' => '12345678Z',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'C/ Mayor 15',
                'postal_code' => '28013',
                'city' => 'Madrid',
            ],
            'buyer' => [
                'party_type' => 'particular',
                'full_name' => 'Comprador Ejemplo',
                'tax_id' => '71234567A',
                'tax_id_country' => 'ES',
                'country' => 'ES',
                'address' => 'C/ Sol 1',
                'postal_code' => '28013',
                'city' => 'Madrid',
            ],
        ];

        $res = $this->actingAs($user)->post(route('contracts.store'), $contractData);
        $res->assertRedirect();

        $contract = Contract::latest()->first();
        $this->assertNotNull($contract);
        $this->assertEquals('arras', $contract->contract_type);

        // Verify generated clauses include Arras Penitenciales art. 1454 CC
        $clauseKeys = array_column($contract->clauses, 'key');
        $this->assertContains('arras_penitenciales', $clauseKeys);
        $this->assertContains('escritura_publica', $clauseKeys);
        $this->assertContains('estado_cargas', $clauseKeys);
        $this->assertContains('distribucion_gastos', $clauseKeys);

        $arrasClause = collect($contract->clauses)->firstWhere('key', 'arras_penitenciales');
        $this->assertStringContainsString('1454 del Código Civil', $arrasClause['body']);
        $this->assertStringContainsString('ARRAS PENITENCIALES', $arrasClause['body']);
    }

    public function test_parses_spanish_dni_4_european_layout_with_labels(): void
    {
        $parser = app(IdentityCardParserService::class);

        $ocrText = "ESPAÑA\n1. APELLIDO / 1st SURNAME\nFERNANDEZ\n2. APELLIDO / 2nd SURNAME\nHERNANDEZ\nNOMBRE / NAME\nCARLOS\nNUM / NO: 01.234.567-L\nVALIDEZ / EXPIRY: 20-12-2035";
        $parsed = $parser->parseText($ocrText);

        $this->assertTrue($parsed['success']);
        $this->assertEquals('front', $parsed['side']);
        $this->assertEquals('01234567L', $parsed['tax_id']);
        $this->assertTrue($parsed['tax_id_valid']);
        $this->assertEquals('Carlos Fernandez Hernandez', $parsed['full_name']);
        $this->assertEquals('Carlos', $parsed['first_name']);
        $this->assertEquals('Fernandez Hernandez', $parsed['last_name']);
    }

    public function test_review_party_update_attaches_scanned_id_cards_to_contract(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $contract = Contract::factory()->create([
            'user_id' => $user->id,
            'status' => 'en_revision',
            'access_token' => 'test-token-review-123',
            'access_token_expires_at' => now()->addDays(7),
        ]);

        // Upload front via scan endpoint
        $fileFront = UploadedFile::fake()->createWithContent('anverso.jpg', 'fake-image-content');
        $scanRes = $this->postJson(route('contracts.scan-id'), [
            'document' => $fileFront,
            'side' => 'front',
            'ocr_text' => "1. APELLIDO\nTORRES\nNOMBRE\nELENA\nDNI 12345678Z",
        ]);
        $scanToken = $scanRes->json('scan_token');

        // Counterparty updates party details on review page
        $res = $this->post(route('review.party.update', 'test-token-review-123'), [
            'role' => 'comprador',
            'party_type' => 'particular',
            'full_name' => 'Elena Torres',
            'tax_id' => '12345678Z',
            'tax_id_country' => 'ES',
            'country' => 'ES',
            'address' => 'Calle Mayor 100',
            'postal_code' => '28013',
            'city' => 'Madrid',
            'id_card_front_token' => $scanToken,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('contract_documents', [
            'contract_id' => $contract->id,
            'requirement_key' => 'dni_partes',
            'filename' => 'Documento_Identidad_comprador_anverso.jpg',
        ]);
    }

    public function test_parses_spanish_dni_3_mrz_with_support_number_and_real_nif_in_line_2(): void
    {
        $parser = app(IdentityCardParserService::class);

        // Real Spanish DNI 3.0 MRZ where line 1 has support number CKL159690 and line 2 has real NIF 12345678Z
        $mrz = "DOMICILIO: CALLE ALCALA 45 2ºB\nMUNICIPIO: MADRID\nCP: 28014\nIDESPCKL159690<<<<<<<<<<<<<<<\n8505152M3005154ESP12345678Z<<<<8\nGARCIA<LOPEZ<<JUAN<CARLOS<<<<<";
        $parsed = $parser->parseText($mrz);

        $this->assertTrue($parsed['success']);
        $this->assertEquals('12345678Z', $parsed['tax_id']);
        $this->assertEquals('CKL159690', $parsed['support_number']);
        $this->assertEquals('ES', $parsed['tax_id_country']);
        $this->assertTrue($parsed['tax_id_valid']);
        $this->assertEquals('Juan Carlos Garcia Lopez', $parsed['full_name']);
    }

    public function test_binary_image_upload_without_ocr_does_not_generate_noise_strings(): void
    {
        $parser = app(IdentityCardParserService::class);

        // Simulating binary JPEG image bytes
        $fakeBinaryImage = UploadedFile::fake()->createWithContent('photo.jpg', "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00\xFF\xDB\x00C\x00Yhne0gpctfPxxwaw9x6l5xngls4truscx7dzm4xpysxf4vbmt9ctey8gcj4xgk7pvbdohykj5z7kbxcl7ayfe");

        $parsed = $parser->parse($fakeBinaryImage, null);

        $this->assertFalse($parsed['success']);
        $this->assertNull($parsed['full_name']);
        $this->assertNull($parsed['tax_id']);
        $this->assertNull($parsed['address']);
    }

    public function test_parses_international_and_latam_identity_documents(): void
    {
        $parser = app(IdentityCardParserService::class);

        // 1. Argentina DNI & CUIT
        $arText = "REPÚBLICA ARGENTINA\nREGISTRO NACIONAL DE LAS PERSONAS\nDOCUMENTO NACIONAL DE IDENTIDAD\nAPELLIDO: GONZALEZ\nNOMBRE: MARTIN\nCUIT: 20-30123456-3\nDOMICILIO: AV CORRIENTES 1234\nLOCALIDAD: BUENOS AIRES\nCP: 1425";
        $arParsed = $parser->parseText($arText);
        $this->assertTrue($arParsed['success']);
        $this->assertEquals('20301234563', str_replace('-', '', $arParsed['tax_id']));
        $this->assertEquals('AR', $arParsed['tax_id_country']);
        $this->assertEquals('Martin Gonzalez', $arParsed['full_name']);
        $this->assertEquals('AV CORRIENTES 1234', $arParsed['address']);
        $this->assertEquals('1425', $arParsed['postal_code']);

        // 2. Mexico INE / RFC
        $mxText = "INSTITUTO NACIONAL ELECTORAL\nCREDENCIAL PARA VOTAR\nNOMBRE: LOPEZ HERNANDEZ SOFIA\nDOMICILIO: CALLE INSURGENTES SUR 1200\nCOLONIA DEL VALLE\nC.P. 03100\nALCALDÍA: BENITO JUAREZ\nESTADO: CIUDAD DE MEXICO\nRFC: LOHS8505151A0";
        $mxParsed = $parser->parseText($mxText);
        $this->assertTrue($mxParsed['success']);
        $this->assertEquals('LOHS8505151A0', $mxParsed['tax_id']);
        $this->assertEquals('MX', $mxParsed['tax_id_country']);
        $this->assertEquals('03100', $mxParsed['postal_code']);
    }
}

