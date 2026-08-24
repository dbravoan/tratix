<?php

namespace Database\Seeders;

use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

/**
 * Catálogo de documentos obligatorios/recomendados por tipo de contrato,
 * con explicaciones en lenguaje llano ("por qué") y pasos a seguir.
 *
 * La clave de cada documento es estable y se usa en contract_documents.
 */
class DocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        DocumentRequirement::query()->delete();

        $this->vehiculosC2c();
        $this->vehiculosB2c();
        $this->inmueblesC2c();
        $this->inmueblesB2c();
        $this->bienesMueblesC2c();
        $this->bienesMueblesB2c();
        $this->serviciosB2b();
        $this->internacional();
        $this->latamGenerics();
        $this->newTypes();
    }

    private function insert(array $rows): void
    {
        foreach ($rows as $row) {
            if (! array_key_exists('country', $row)) {
                $row['country'] = 'ES';
            }
            DocumentRequirement::updateOrCreate(
                [
                    'contract_type' => $row['contract_type'],
                    'transaction_type' => $row['transaction_type'] ?? null,
                    'jurisdiction' => $row['jurisdiction'] ?? null,
                    'country' => $row['country'],
                    'key' => $row['key'],
                ],
                $row
            );
        }
    }

    private function vehiculosC2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato privado de compraventa firmado',
                'purpose' => 'Es el documento principal que prueba que vendiste el coche. Es lo que vas a generar y firmar aquí, con validez para el cambio de titularidad.',
                'steps' => 'Se genera y firma en esta misma aplicación con la firma de ambas partes y la hoja de evidencias.',
                'legal_note' => 'Art. 1254 y ss. Código Civil; necesaria para el trámite de transferencia ante la DGT.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes (anverso y reverso)',
                'purpose' => 'La DGT necesita identificar a comprador y vendedor para inscribir el cambio de titularidad.',
                'steps' => 'Escanea o fotografía el DNI/NIE por ambas caras. Si algún titular no puede aportarlo, usa el certificado digital en su lugar.',
                'legal_note' => 'Reglamento General de Conductores y normativa de tráfico.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 3,
                'key' => 'informe_trafico', 'title' => 'Informe de tráfico (certificado de titularidad y cargas)',
                'purpose' => 'Confirma que el vendedor es el titular y que el coche no tiene embargos, precintos o cargas que impidan la venta.',
                'steps' => 'Se pide en la sede electrónica de la DGT (informe de vehículo) o en una Jefatura Provincial de Tráfico. Es instantáneo y gratuito en la sede.',
                'legal_note' => 'Ley sobre Tráfico, Circulación y Seguridad Vial.',
                'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 4,
                'key' => 'ficha_tecnica', 'title' => 'Ficha técnica / permiso de circulación',
                'purpose' => 'Acredita las características del vehículo (marca, modelo, bastidor, cilindrada). El comprador la necesita para el nuevo permiso.',
                'steps' => 'La entrega el vendedor con el vehículo. Comprueba que los datos coinciden con el vehículo y el informe de tráfico.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 5,
                'key' => 'itv', 'title' => 'ITV en vigor (si procede)',
                'purpose' => 'Un coche sin ITV no puede circular ni transferirse. Si está caducada hay que pasarla antes del cambio de titularidad.',
                'steps' => 'Revisa la fecha de caducidad en la pegatina. Si vence antes del trámite, pide cita en la ITV más cercana.',
                'legal_note' => 'Real Decreto 920/2017, art. 4 y 5.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 6,
                'key' => 'justificante_pago', 'title' => 'Justificante del pago (transferencia o cheque)',
                'purpose' => 'Prueba que el comprador pagó el precio pactado. Te protege frente a reclamaciones y es útil para Hacienda.',
                'steps' => 'Conserva el justificante de la transferencia o una copia del cheque bancario con el número de operación.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 7,
                'key' => 'itp', 'title' => 'Autoliquidación del ITP (Impuesto de Transmisiones Patrimoniales)',
                'purpose' => 'Entre particulares la venta no lleva IVA, pero el comprador debe pagar el ITP de su comunidad autónoma. Hay que liquidarlo en 30 días hábiles desde la firma.',
                'steps' => '1) Descarga el modelo 620 de tu comunidad autónoma. 2) Calcula el impuesto (aprox. 4 % del valor, según CCAA). 3) Líquidalo online y guarda el justificante.',
                'legal_note' => 'RDL 1/1993, art. 7. Plazo de 30 días hábiles desde el devengo.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 8,
                'key' => 'tasa_dgt', 'title' => 'Justificante de la tasa de la DGT por el cambio de titularidad',
                'purpose' => 'La transferencia de un vehículo tiene una tasa (entre unos 50 y 100 € según la comunidad). Hay que pagarla antes de solicitar el trámite.',
                'steps' => 'Paga la tasa telemáticamente en la sede de la DGT (trámite TIV) o en la Jefatura. Se abona en el momento de iniciar el cambio de titularidad.',
                'legal_note' => 'Ley 16/1979 sobre tasas de tráfico.',
                'link_label' => 'Trámite TIV en la DGT', 'link_url' => 'https://sede.dgt.gob.es/es/tramites-y-multas/transferencia-de-vehiculos/', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 9,
                'key' => 'cambio_titularidad', 'title' => 'Justificante del cambio de titularidad (DGT)',
                'purpose' => 'Es el paso definitivo: la DGT inscribe al comprador como nuevo titular. Hasta que no esté hecho, la responsabilidad del coche sigue siendo del vendedor.',
                'steps' => '1) Entra en la sede electrónica de la DGT. 2) Trámite de transferencia de vehículos (TIV). 3) Sube el contrato firmado, el DNI de ambas partes y la ficha técnica. 4) Si no tienes certificado digital, acude a una Jefatura con cita previa. 5) Guarda el acuse.',
                'legal_note' => 'Transferencia obligatoria del titular del vehículo (art. 10 RD 2822/1998).',
                'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'order' => 10,
                'key' => 'informe_final', 'title' => 'Informe de tráfico final (verificación del nuevo titular)',
                'purpose' => 'Comprueba que el cambio de titularidad se ha completado y que ya no figuras como propietario.',
                'steps' => 'Pasados unos días del trámite, solicita de nuevo el informe de vehículo en la DGT y confirma que aparece el comprador como titular.',
                'legal_note' => null, 'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => false,
            ],
        ]);
    }

    private function vehiculosB2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de compraventa firmado',
                'purpose' => 'Documento que prueba la venta entre el profesional y el consumidor.',
                'steps' => 'Se genera y firma en esta aplicación con la hoja de evidencias.',
                'legal_note' => 'RDL 1/2007 (LGDCU) y Código Civil.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes',
                'purpose' => 'Identificación necesaria para el cambio de titularidad en la DGT.',
                'steps' => 'Fotografía el DNI/NIE por ambas caras.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 3,
                'key' => 'informe_trafico', 'title' => 'Informe de tráfico',
                'purpose' => 'Verifica titularidad y ausencia de cargas del vehículo.',
                'steps' => 'Se obtiene gratis en la sede electrónica de la DGT.',
                'legal_note' => null, 'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 4,
                'key' => 'factura', 'title' => 'Factura del vendedor profesional',
                'purpose' => 'La venta de un profesional lleva IVA. La factura es obligatoria y necesaria para el ITP/garantía.',
                'steps' => 'El vendedor emite factura conforme al RD 1619/2012 y la entrega con el contrato.',
                'legal_note' => 'Ley 37/1992 del IVA; RD 1619/2012.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 5,
                'key' => 'ficha_tecnica', 'title' => 'Ficha técnica / permiso de circulación',
                'purpose' => 'Características del vehículo para el nuevo permiso de circulación.',
                'steps' => 'La entrega el vendedor con el vehículo.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 6,
                'key' => 'itv', 'title' => 'ITV en vigor',
                'purpose' => 'Requisito para circular y transferir el vehículo.',
                'steps' => 'Revisa la caducidad; si procede, pásala antes del trámite.',
                'legal_note' => 'RD 920/2017.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 7,
                'key' => 'tasa_dgt', 'title' => 'Justificante de la tasa DGT',
                'purpose' => 'Tasa por el cambio de titularidad, abonada por el comprador.',
                'steps' => 'Se paga en la sede DGT o en Jefatura al iniciar el trámite.',
                'legal_note' => 'Ley 16/1979.',
                'link_label' => 'Trámite TIV', 'link_url' => 'https://sede.dgt.gob.es/es/tramites-y-multas/transferencia-de-vehiculos/', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 8,
                'key' => 'cambio_titularidad', 'title' => 'Justificante del cambio de titularidad (DGT)',
                'purpose' => 'Inscripción del comprador como nuevo titular.',
                'steps' => 'Trámite TIV en la sede electrónica o en Jefatura con cita.',
                'legal_note' => 'Art. 10 RD 2822/1998.',
                'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'b2c', 'order' => 9,
                'key' => 'informe_final', 'title' => 'Informe de tráfico final',
                'purpose' => 'Verificación de que el cambio se ha completado.',
                'steps' => 'Consulta el informe pasados unos días del trámite.',
                'legal_note' => null, 'link_label' => 'Sede electrónica DGT', 'link_url' => 'https://sede.dgt.gob.es', 'mandatory' => false,
            ],
        ]);
    }

    private function inmueblesC2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato privado de compraventa firmado',
                'purpose' => 'Documento que fija el acuerdo y las condiciones (precio, cargas, plazo).',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'Art. 1279 y 1451 CC. Para la escritura pública, art. 1280 CC.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes',
                'purpose' => 'Identificación de vendedor y comprador para el contrato y la notaría.',
                'steps' => 'Fotografía el DNI/NIE por ambas caras.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 3,
                'key' => 'nota_simple', 'title' => 'Nota simple del Registro de la Propiedad',
                'purpose' => 'Acredita quién es el dueño registral y si el piso tiene hipotecas, embargos u otras cargas.',
                'steps' => 'Se solicita en el Registro de la Propiedad correspondiente (online o presencial).',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 4,
                'key' => 'escritura_anterior', 'title' => 'Escritura de compra del vendedor',
                'purpose' => 'Muestra la titularidad y la descripción registral del inmueble.',
                'steps' => 'La guarda el vendedor. Es imprescindible para preparar la nueva escritura.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 5,
                'key' => 'certificado_eficiencia', 'title' => 'Certificado de eficiencia energética',
                'purpose' => 'Obligatorio en toda venta de vivienda; informa del consumo energético.',
                'steps' => 'Lo emite un técnico autorizado. Hay que entregarlo al comprador antes de la venta.',
                'legal_note' => 'RD 390/2021.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 6,
                'key' => 'referencia_catastral', 'title' => 'Referencia catastral y recibo del IBI',
                'purpose' => 'Identifica la finca a efectos catastrales y confirma el pago de impuestos municipales.',
                'steps' => 'La referencia figura en el recibo del IBI. Adjunta el último recibo pagado.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 7,
                'key' => 'cedula_habitabilidad', 'title' => 'Cédula de habitabilidad (si existe)',
                'purpose' => 'Certifica que la vivienda reúne condiciones mínimas de habitabilidad (según CCAA).',
                'steps' => 'Se solicita en la comunidad autónoma correspondiente.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 8,
                'key' => 'itp', 'title' => 'Autoliquidación del ITP (si no hay IVA)',
                'purpose' => 'Entre particulares la compraventa tributa por ITP (6-10 % según CCAA) que paga el comprador, en 30 días hábiles.',
                'steps' => 'Descarga el modelo 600 de tu CCAA, liquida y guarda el justificante.',
                'legal_note' => 'RDL 1/1993, art. 7.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 9,
                'key' => 'escritura_notarial', 'title' => 'Escritura pública ante notario',
                'purpose' => 'Para inscribir la compra en el Registro de la Propiedad es recomendable elevarla a escritura pública.',
                'steps' => 'Pide cita en la notaría con la nota simple, el contrato y los DNI. El notario prepara la escritura.',
                'legal_note' => 'Art. 1280 CC.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'order' => 10,
                'key' => 'plusvalia', 'title' => 'Liquidación de la plusvalía municipal (IIVTNU)',
                'purpose' => 'Impuesto municipal sobre la ganancia de la venta; suele pagarlo el vendedor.',
                'steps' => 'Consúltalo en tu ayuntamiento y guarda el justificante de pago.',
                'legal_note' => 'RDL 2/2004 (TRLHL).',
                'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
        ]);
    }

    private function inmueblesB2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de compraventa firmado',
                'purpose' => 'Acuerdo entre promotor/profesional y comprador.',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'RDL 1/2007 (LGDCU).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes',
                'purpose' => 'Identificación para contrato y notaría.',
                'steps' => 'Fotografía el DNI/NIE por ambas caras.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 3,
                'key' => 'nota_simple', 'title' => 'Nota simple del Registro',
                'purpose' => 'Cargas y titularidad registral.',
                'steps' => 'Se solicita en el Registro de la Propiedad.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 4,
                'key' => 'certificado_eficiencia', 'title' => 'Certificado de eficiencia energética',
                'purpose' => 'Obligatorio en ventas de vivienda (etiqueta energética).',
                'steps' => 'Lo emite un técnico autorizado.',
                'legal_note' => 'RD 390/2021.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 5,
                'key' => 'iva_factura', 'title' => 'Factura con IVA (vivienda de obra nueva)',
                'purpose' => 'La primera entrega de vivienda tributa IVA al 10 %.',
                'steps' => 'El promotor emite factura con IVA.',
                'legal_note' => 'Ley 37/1992 del IVA.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 6,
                'key' => 'escritura_notarial', 'title' => 'Escritura pública ante notario',
                'purpose' => 'Inscripción registral de la compra.',
                'steps' => 'Cita en la notaría con la documentación.',
                'legal_note' => 'Art. 1280 CC.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'b2c', 'order' => 7,
                'key' => 'ajd', 'title' => 'AJD (Actos Jurídicos Documentados)',
                'purpose' => 'Impuesto de la escritura cuando hay IVA, que paga el comprador.',
                'steps' => 'Se liquida con la escritura en la CCAA.',
                'legal_note' => 'RDL 1/1993.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
        ]);
    }

    private function bienesMueblesC2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato privado firmado',
                'purpose' => 'Prueba de la venta y las condiciones pactadas.',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'Código Civil, arts. 1445 y ss.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes',
                'purpose' => 'Identificación de las partes.',
                'steps' => 'Fotografía el DNI/NIE por ambas caras.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'order' => 3,
                'key' => 'justificante_pago', 'title' => 'Justificante del pago',
                'purpose' => 'Prueba del pago del precio.',
                'steps' => 'Conserva el justificante de transferencia o el recibo.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'order' => 4,
                'key' => 'factura_original', 'title' => 'Factura original o garantía (si existe)',
                'purpose' => 'Para bienes con garantía en vigor, facilita el traspaso al comprador.',
                'steps' => 'Entrega la factura y el documento de garantía.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
        ]);
    }

    private function bienesMueblesB2c(): void
    {
        $this->insert([
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'b2c', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de compraventa firmado',
                'purpose' => 'Acuerdo de venta entre profesional y consumidor.',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'RDL 1/2007.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'b2c', 'order' => 2,
                'key' => 'factura', 'title' => 'Factura del vendedor',
                'purpose' => 'Obligatoria en ventas profesionales; base de la garantía legal.',
                'steps' => 'El vendedor la emite y entrega al consumidor.',
                'legal_note' => 'RD 1619/2012; RDL 1/2007.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'b2c', 'order' => 3,
                'key' => 'garantia', 'title' => 'Documento de garantía legal (2-3 años)',
                'purpose' => 'El consumidor tiene derecho a reparación o sustitución durante la garantía.',
                'steps' => 'Entrega el documento de garantía con la venta.',
                'legal_note' => 'RDL 1/2007, art. 114 y ss.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'b2c', 'order' => 4,
                'key' => 'desistimiento', 'title' => 'Información sobre el derecho de desistimiento',
                'purpose' => 'En ventas a distancia o fuera de establecimiento el consumidor puede desistir en 14 días.',
                'steps' => 'Añade el formulario de desistimiento y el aviso de plazos.',
                'legal_note' => 'RDL 1/2007, art. 102 y ss.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
        ]);
    }

    private function serviciosB2b(): void
    {
        $this->insert([
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de prestación de servicios firmado',
                'purpose' => 'Define el alcance del servicio, plazos y condiciones comerciales.',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'Código de Comercio; Ley 3/2004 de morosidad.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'order' => 2,
                'key' => 'cif_partes', 'title' => 'CIF/NIF de ambas empresas',
                'purpose' => 'Identificación fiscal de las partes para facturación.',
                'steps' => 'Adjunta el NIF de las dos empresas.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'order' => 3,
                'key' => 'factura', 'title' => 'Factura del servicio',
                'purpose' => 'Documento fiscal obligatorio de la operación.',
                'steps' => 'Emite factura conforme al RD 1619/2012 al completar el servicio (o según pacto).',
                'legal_note' => 'RD 1619/2012.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'order' => 4,
                'key' => 'albaran', 'title' => 'Albarán / conformidad del servicio',
                'purpose' => 'Acredita la recepción y conformidad del servicio prestado.',
                'steps' => 'Ambas partes firman el albarán al finalizar.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
        ]);
    }

    private function internacional(): void
    {
        $this->insert([
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de compraventa internacional firmado',
                'purpose' => 'Regula la venta entre empresas de distintos países (posible CISG).',
                'steps' => 'Se genera y firma en esta aplicación.',
                'legal_note' => 'Convención de Viena (CISG) 1980 si aplica; Reglamento Roma I 593/2008.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 2,
                'key' => 'vat_numbers', 'title' => 'Números de IVA de ambas partes',
                'purpose' => 'Necesarios para el tratamiento fiscal (exportación exenta o inversión del sujeto pasivo).',
                'steps' => 'Verifica ambos números en VIES.',
                'legal_note' => 'Ley 37/1992, art. 21 y 84.',
                'link_label' => 'VIES', 'link_url' => 'https://ec.europa.eu/taxation_customs/vies/', 'mandatory' => true,
            ],
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 3,
                'key' => 'factura_comercial', 'title' => 'Factura comercial (commercial invoice)',
                'purpose' => 'Documento base para la exportación y el despacho de aduanas.',
                'steps' => 'Emite la factura con Incoterms pactado y valores.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 4,
                'key' => 'documento_transporte', 'title' => 'Documento de transporte (BL/CMR/AWB)',
                'purpose' => 'Prueba la entrega y es base del Incoterm.',
                'steps' => 'El transportista lo emite al cargar la mercancía.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 5,
                'key' => 'despacho_aduanas', 'title' => 'Documentación de despacho de aduanas',
                'purpose' => 'Dependiendo del Incoterm, una de las partes gestiona la exportación/importación.',
                'steps' => 'Usa un agente de aduanas (EAE) para el despacho.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'internacional', 'transaction_type' => 'b2b', 'jurisdiction' => 'internacional', 'order' => 6,
                'key' => 'certificado_origen', 'title' => 'Certificado de origen (si aplica)',
                'purpose' => 'Necesario para aranceles preferenciales en algunos acuerdos.',
                'steps' => 'Lo emite la Cámara de Comercio.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
        ]);
    }

    /**
     * Generic guidance for Latin American jurisdictions (AR, MX, CO, CL, PE, UY).
     * Specific rows per country can be added later with 'country' => 'MX', etc.
     * These rows are used as fallback when no country-specific list exists.
     */
    private function latamGenerics(): void
    {
        $this->insert([
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'country' => null, 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato privado de compraventa firmado',
                'purpose' => 'Documento principal que prueba la transmisión del vehículo. Se genera y firma aquí con hoja de evidencias.',
                'steps' => 'Se firma en esta aplicación por ambas partes.',
                'legal_note' => 'Base del trámite de transferencia ante el registro de vehículos local.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'country' => null, 'order' => 2,
                'key' => 'identidad_partes', 'title' => 'Documentos de identidad de ambas partes',
                'purpose' => 'El registro local de vehículos exige identificar a comprador y vendedor.',
                'steps' => 'Aporta la cédula/DNI/RUT de cada parte.',
                'legal_note' => 'Requisito de los registros automotores de la región.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'country' => null, 'order' => 3,
                'key' => 'titulo_vehiculo', 'title' => 'Título de propiedad del vehículo (cédula/tarjeta)',
                'purpose' => 'Acredita que el vendedor es el titular inscrito y puede transmitirlo.',
                'steps' => 'Revisa que los datos coincidan con el documento de identidad.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'country' => null, 'order' => 4,
                'key' => 'verificacion_cargas', 'title' => 'Informe de deudas, multas y gravámenes del vehículo',
                'purpose' => 'Evita sorpresas: multas, prendas o embargos pendientes.',
                'steps' => 'Consúltalo en el organismo de tránsito o registro local.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'vehiculos', 'transaction_type' => 'c2c', 'country' => null, 'order' => 5,
                'key' => 'cambio_titularidad', 'title' => 'Trámite de cambio de titularidad (transferencia)',
                'purpose' => 'Es el paso que legaliza al nuevo dueño ante el registro de vehículos.',
                'steps' => 'Preséntalo ante el registro/entidad de tránsito local con el contrato firmado.',
                'legal_note' => 'Obligatorio para que la venta tenga plenos efectos frente a terceros.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],

            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato privado de compraventa firmado',
                'purpose' => 'Prueba del acuerdo de compraventa mientras se formaliza ante notario/registro.',
                'steps' => 'Se firma aquí con hoja de evidencias.',
                'legal_note' => 'Los Códigos Civiles locales reconocen el contrato privado entre partes.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 2,
                'key' => 'titulo_propiedad', 'title' => 'Título de propiedad (escritura / folio real)',
                'purpose' => 'El vendedor debe acreditar que es el dueño inscrito.',
                'steps' => 'Aporta la escritura o certificado de dominio del registro local.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 3,
                'key' => 'certificado_libertad', 'title' => 'Certificado de libertad y gravámenes',
                'purpose' => 'Acredita que el inmueble no tiene hipotecas, embargos ni anotaciones.',
                'steps' => 'Solicítalo en el registro de la propiedad competente.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 4,
                'key' => 'impuestos_pendientes', 'title' => 'Pago de impuestos municipales/prediales al día',
                'purpose' => 'Evita reclamaciones por deudas de contribución o predial.',
                'steps' => 'Pide el certificado de no deuda del municipio.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'inmuebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 5,
                'key' => 'escritura_notarial', 'title' => 'Escritura pública y registro de la compraventa',
                'purpose' => 'La transmisión de inmuebles requiere formalización notarial para su inscripción.',
                'steps' => 'Acude con ambas partes al notario o escribano y luego inscribe en el registro.',
                'legal_note' => 'Los notarios locales son obligatorios para inmuebles.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],

            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de compraventa firmado',
                'purpose' => 'Documento principal que prueba la transmisión y sus condiciones.',
                'steps' => 'Se firma aquí por ambas partes.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 2,
                'key' => 'identidad_partes', 'title' => 'Documentos de identidad de ambas partes',
                'purpose' => 'Identifica a los intervinientes en caso de reclamación.',
                'steps' => 'Aporta los documentos de identidad de cada parte.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 3,
                'key' => 'justificante_pago', 'title' => 'Justificante del pago',
                'purpose' => 'Acredita que el precio fue abonado y evita discrepancias.',
                'steps' => 'Guarda la transferencia bancaria o el recibo firmado.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'bienes_muebles', 'transaction_type' => 'c2c', 'country' => null, 'order' => 4,
                'key' => 'entrega_acta', 'title' => 'Acta o recibo de entrega',
                'purpose' => 'Confirma que el bien fue entregado en la fecha y estado pactados.',
                'steps' => 'Firma un pequeño acta de entrega con fecha.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],

            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'country' => null, 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de servicios firmado',
                'purpose' => 'Define alcance, plazos y precio del servicio.',
                'steps' => 'Se firma aquí por ambas partes.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'country' => null, 'order' => 2,
                'key' => 'factura_fiscal', 'title' => 'Factura fiscal y comprobantes',
                'purpose' => 'Requisito fiscal y prueba del precio acordado.',
                'steps' => 'El proveedor emite la factura conforme a la normativa local.',
                'legal_note' => 'Exigible según la legislación fiscal local (IVa/IGV/IVA).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true,
            ],
            [
                'contract_type' => 'servicios', 'transaction_type' => 'b2b', 'country' => null, 'order' => 3,
                'key' => 'conformidad_entrega', 'title' => 'Acta de conformidad / entrega del servicio',
                'purpose' => 'Cierra el servicio y evita disputas sobre el cumplimiento.',
                'steps' => 'Firma un acta de conformidad al recibir el resultado.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false,
            ],
        ]);
    }

    /**
     * Guidance for the newer contract types (alquiler, préstamo, cesión, NDA).
     * Generic rows (country = null) serve as fallback for all jurisdictions.
     */
    private function newTypes(): void
    {
        $this->insert([
            // Alquiler
            ['contract_type' => 'alquiler', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de arrendamiento firmado',
                'purpose' => 'Documento principal que fija la renta, la duración y las obligaciones de ambas partes.',
                'steps' => 'Se firma aquí con firma electrónica y hoja de evidencias.',
                'legal_note' => 'Ley 29/1994 de Arrendamientos Urbanos (LAU) para vivienda; Código Civil para arrendamientos no urbanos.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'alquiler', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 2,
                'key' => 'identificacion_partes', 'title' => 'DNI/NIE de ambas partes',
                'purpose' => 'Identifica a arrendador y arrendatario.',
                'steps' => 'Aporta los documentos de identidad.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false],
            ['contract_type' => 'alquiler', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 3,
                'key' => 'justificante_pago_fianza', 'title' => 'Justificante del depósito de la fianza',
                'purpose' => 'La fianza (normalmente una mensualidad) debe depositarse en el organismo autonómico competente.',
                'steps' => 'Realiza el depósito y conserva el justificante.',
                'legal_note' => 'Art. 36 LAU y normativa autonómica de fianzas.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],

            // Préstamo
            ['contract_type' => 'prestamo', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de préstamo firmado',
                'purpose' => 'Documento que acredita la entrega del dinero y la obligación de devolución.',
                'steps' => 'Se firma aquí con firma electrónica.',
                'legal_note' => 'Arts. 1740 y ss. Código Civil (préstamo simple / mutuo).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'prestamo', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 2,
                'key' => 'justificante_entrega', 'title' => 'Justificante de la entrega del dinero',
                'purpose' => 'Prueba de que el importe fue efectivamente entregado.',
                'steps' => 'Conserva la transferencia bancaria o recibo firmado.',
                'legal_note' => 'Los préstamos de más de 3.000 € suelen requerir prueba escrita.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],

            // Cesión de derechos
            ['contract_type' => 'cesion_derechos', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de cesión firmado',
                'purpose' => 'Acredita la transmisión de los derechos y su alcance.',
                'steps' => 'Se firma aquí con firma electrónica.',
                'legal_note' => 'Arts. 1526 y ss. Código Civil (cesión de créditos y derechos).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],

            // NDA
            ['contract_type' => 'nda', 'transaction_type' => 'b2b', 'country' => 'ES', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Acuerdo de confidencialidad firmado',
                'purpose' => 'Protege la información sensible compartida entre las partes.',
                'steps' => 'Se firma aquí con firma electrónica.',
                'legal_note' => 'Libertad contractual (art. 1255 Código Civil).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'nda', 'transaction_type' => 'b2b', 'country' => 'ES', 'order' => 2,
                'key' => 'anexo_informacion', 'title' => 'Anexo con la información confidencial',
                'purpose' => 'Define qué información queda protegida.',
                'steps' => 'Adjunta el detalle de la información confidencial.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => false],

            // Arras
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 1,
                'key' => 'contrato_firmado', 'title' => 'Contrato de arras penitenciales firmado',
                'purpose' => 'Documento privado preparatorio que fija la reserva del inmueble, el precio total, el importe de la señal y el plazo para la escritura pública.',
                'steps' => 'Se genera y firma en esta aplicación con firma electrónica y hoja de evidencias.',
                'legal_note' => 'Art. 1454 Código Civil (arras penitenciales).',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 2,
                'key' => 'dni_partes', 'title' => 'DNI/NIE de ambas partes (anverso y reverso)',
                'purpose' => 'Identificación de comprador y vendedor para el contrato y la futura notaría.',
                'steps' => 'Escanea o fotografía el DNI/NIE de ambas partes por anverso y reverso.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 3,
                'key' => 'nota_simple', 'title' => 'Nota simple del Registro de la Propiedad',
                'purpose' => 'Acredita quién es el titular registral y si el inmueble tiene hipotecas, embargos o cargas a cancelar.',
                'steps' => 'Solicítala online en registradores.org o en el Registro de la Propiedad.',
                'legal_note' => 'Imprescindible para verificar titularidad y cargas antes de entregar la señal.',
                'link_label' => 'Sede Registradores de España', 'link_url' => 'https://www.registradores.org', 'mandatory' => true],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 4,
                'key' => 'justificante_arras', 'title' => 'Justificante de pago de las arras (transferencia o cheque)',
                'purpose' => 'Prueba bancaria de que la señal o reserva fue efectivamente abonada al vendedor.',
                'steps' => 'Conserva el comprobante de transferencia bancaria con el número de operación.',
                'legal_note' => null, 'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 5,
                'key' => 'recibo_ibi', 'title' => 'Último recibo del IBI pagado',
                'purpose' => 'Confirma que el inmueble está al corriente del Impuesto sobre Bienes Inmuebles.',
                'steps' => 'El vendedor debe aportar el último recibo o certificado de no deuda del ayuntamiento.',
                'legal_note' => 'Exigido por la Notaría para la escritura pública.',
                'link_label' => null, 'link_url' => null, 'mandatory' => true],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 6,
                'key' => 'certificado_comunidad', 'title' => 'Certificado de comunidad al corriente de pago',
                'purpose' => 'Acredita que no existen deudas por cuotas ordinarias o derramas de la comunidad de propietarios.',
                'steps' => 'Lo emite el administrador o presidente de la comunidad de propietarios.',
                'legal_note' => 'Art. 9.1.e Ley de Propiedad Horizontal (LPH).',
                'link_label' => null, 'link_url' => null, 'mandatory' => false],
            ['contract_type' => 'arras', 'transaction_type' => 'c2c', 'country' => 'ES', 'order' => 7,
                'key' => 'certificado_eficiencia', 'title' => 'Certificado de eficiencia energética',
                'purpose' => 'Obligatorio para la venta de inmuebles residenciales en España.',
                'steps' => 'Emitido por técnico competente colegiado.',
                'legal_note' => 'RD 390/2021.',
                'link_label' => null, 'link_url' => null, 'mandatory' => false],
        ]);
    }
}
