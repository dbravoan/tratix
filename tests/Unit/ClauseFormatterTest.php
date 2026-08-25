<?php

namespace Tests\Unit;

use App\Services\ClauseFormatter;
use Tests\TestCase;

class ClauseFormatterTest extends TestCase
{
    public function test_formats_legacy_inline_bullet_text_into_clean_html_lists(): void
    {
        $raw = "VENDEDOR — DERECHOS: • Derecho a recibir el precio pactado (3.000,00 EUR) en la forma y plazos convenidos. • Derecho a que el comprador reciba el bien y colabore en los trámites formales (entrega, registro o transferencia). OBLIGACIONES: • Entregar el bien objeto del contrato en el estado y plazo pactados. • Responder del saneamiento por vicios ocultos (arts. 1484-1499 del Código Civil). COMPRADOR — DERECHOS: • Derecho a recibir el bien en el estado descrito y dentro del plazo acordado por el precio de 3.000,00 EUR.";

        $htmlWeb = ClauseFormatter::formatHtml($raw, false);

        $this->assertStringContainsString('VENDEDOR — DERECHOS:', $htmlWeb);
        $this->assertStringContainsString('OBLIGACIONES:', $htmlWeb);
        $this->assertStringContainsString('COMPRADOR — DERECHOS:', $htmlWeb);
        $this->assertStringContainsString('<ul', $htmlWeb);
        $this->assertStringContainsString('<li', $htmlWeb);
        $this->assertStringContainsString('Derecho a recibir el precio pactado (3.000,00 EUR)', $htmlWeb);
        $this->assertStringContainsString('Responder del saneamiento por vicios ocultos', $htmlWeb);

        $htmlPdf = ClauseFormatter::formatHtml($raw, true);
        $this->assertStringContainsString('<ul', $htmlPdf);
        $this->assertStringContainsString('<li', $htmlPdf);
        $this->assertStringContainsString('clause-section-header', $htmlPdf);
    }

    public function test_formats_multiline_structured_clause_cleanly(): void
    {
        $raw = "1. PARTE VENDEDORA (Juan Perez):\n   a) DERECHOS:\n      • Derecho al cobro del precio.\n      • Derecho a exigir la formalización.\n   b) OBLIGACIONES:\n      • Entrega del vehículo en plazo.\n2. PARTE COMPRADORA (Laura Gomez):\n   a) DERECHOS:\n      • Recepción del bien libre de cargas.\n3. MARCO Y REFERENCIAS LEGALES APLICABLES:\n   • Código Civil.";

        $html = ClauseFormatter::formatHtml($raw, false);

        $this->assertStringContainsString('1. PARTE VENDEDORA (Juan Perez):', $html);
        $this->assertStringContainsString('a) DERECHOS:', $html);
        $this->assertStringContainsString('b) OBLIGACIONES:', $html);
        $this->assertStringContainsString('Derecho al cobro del precio.', $html);
        $this->assertStringContainsString('Entrega del vehículo en plazo.', $html);
        $this->assertStringContainsString('3. MARCO Y REFERENCIAS LEGALES APLICABLES:', $html);
    }

    public function test_formats_plain_paragraphs_without_lists(): void
    {
        $raw = "De una parte, como VENDEDOR, Laura Navarro con DNI 12345678Z.\n\nDe otra parte, como COMPRADOR, Pedro Garcia.";
        $html = ClauseFormatter::formatHtml($raw, false);

        $this->assertStringContainsString('<p', $html);
        $this->assertStringNotContainsString('<ul', $html);
        $this->assertStringContainsString('De una parte, como VENDEDOR', $html);
    }

    public function test_handles_null_and_empty_strings(): void
    {
        $this->assertEquals('', ClauseFormatter::formatHtml(null));
        $this->assertEquals('', ClauseFormatter::formatHtml(''));
        $this->assertEquals('', ClauseFormatter::formatHtml('   '));
    }
}
