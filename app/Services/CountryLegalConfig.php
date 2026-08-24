<?php

namespace App\Services;

/**
 * Legal configuration per country (Spain + Latin America).
 *
 * Centralises everything that changes from one jurisdiction to another so the
 * clause builder, validators and guidance are fully country-aware. Spain is the
 * reference jurisdiction; Latin American jurisdictions are progressively added.
 */
class CountryLegalConfig
{
    public const COUNTRIES = ['ES', 'AR', 'MX', 'CO', 'CL', 'PE', 'UY'];

    /**
     * @return array<string, mixed>
     */
    public function config(string $country): array
    {
        $country = strtoupper($country);

        return match ($country) {
            'ES' => [
                'code' => 'ES',
                'name' => 'España',
                'currency' => 'EUR',
                'tax_name' => 'IVA',
                'tax_id_label' => 'NIF/CIF/NIE',
                'company_tax_id_label' => 'CIF',
                'consumer_law' => 'Ley General para la Defensa de los Consumidores y Usuarios (Real Decreto Legislativo 1/2007)',
                'civil_code' => 'Código Civil español',
                'commercial_code' => 'Código de Comercio español',
                'data_protection_law' => 'Reglamento (UE) 2016/679 (RGPD) y Ley Orgánica 3/2018 (LOPDGDD)',
                'transfer_tax' => 'Impuesto sobre Transmisiones Patrimoniales (RDL 1/1993)',
                'jurisdiction_clause' => 'Juzgados y Tribunales de :city (España)',
                'warranty_hidden_defects' => 'arts. 1484-1499 del Código Civil',
                'consumer_warranty_years' => 'tres años para bienes entregados desde el 1 de enero de 2022 (dos años antes)',
                'eu' => true,
            ],
            'AR' => [
                'code' => 'AR',
                'name' => 'Argentina',
                'currency' => 'ARS',
                'tax_name' => 'IVA',
                'tax_id_label' => 'CUIT/CUIL',
                'company_tax_id_label' => 'CUIT',
                'consumer_law' => 'Ley de Defensa del Consumidor (Ley 24.240 y modificatorias)',
                'civil_code' => 'Código Civil y Comercial de la Nación',
                'commercial_code' => 'Código Civil y Comercial de la Nación',
                'data_protection_law' => 'Ley 25.326 de Protección de los Datos Personales',
                'transfer_tax' => 'Impuesto sobre los Ingresos Brutos e ITP según provincia',
                'jurisdiction_clause' => 'Tribunales ordinarios de la Ciudad de :city (Argentina)',
                'warranty_hidden_defects' => 'arts. 1034-1042 del Código Civil y Comercial',
                'consumer_warranty_years' => 'seis meses para bienes muebles (art. 11 Ley 24.240)',
                'eu' => false,
            ],
            'MX' => [
                'code' => 'MX',
                'name' => 'México',
                'currency' => 'MXN',
                'tax_name' => 'IVA',
                'tax_id_label' => 'RFC',
                'company_tax_id_label' => 'RFC',
                'consumer_law' => 'Ley Federal de Protección al Consumidor',
                'civil_code' => 'Código Civil Federal y códigos civiles estatales',
                'commercial_code' => 'Código de Comercio',
                'data_protection_law' => 'Ley Federal de Protección de Datos Personales en Posesión de los Particulares',
                'transfer_tax' => 'Impuesto Sobre Adquisición de Inmuebles (ISAI, estatal)',
                'jurisdiction_clause' => 'Tribunales competentes de :city (México)',
                'warranty_hidden_defects' => 'arts. 2126-2142 del Código Civil Federal',
                'consumer_warranty_years' => 'la garantía se rige por la Ley Federal de Protección al Consumidor y las bases de la operación',
                'eu' => false,
            ],
            'CO' => [
                'code' => 'CO',
                'name' => 'Colombia',
                'currency' => 'COP',
                'tax_name' => 'IVA',
                'tax_id_label' => 'NIT / cédula',
                'company_tax_id_label' => 'NIT',
                'consumer_law' => 'Estatuto del Consumidor (Ley 1480 de 2011)',
                'civil_code' => 'Código Civil colombiano',
                'commercial_code' => 'Código de Comercio colombiano',
                'data_protection_law' => 'Ley 1581 de 2012 (habeas data)',
                'transfer_tax' => 'Impuesto de registro y notariado (según departamento)',
                'jurisdiction_clause' => 'Jueces de la República de Colombia con sede en :city',
                'warranty_hidden_defects' => 'arts. 1881-1905 del Código Civil',
                'consumer_warranty_years' => 'la garantía legal se rige por la Ley 1480 de 2011',
                'eu' => false,
            ],
            'CL' => [
                'code' => 'CL',
                'name' => 'Chile',
                'currency' => 'CLP',
                'tax_name' => 'IVA',
                'tax_id_label' => 'RUT',
                'company_tax_id_label' => 'RUT',
                'consumer_law' => 'Ley sobre Protección de los Derechos de los Consumidores (Ley 19.496)',
                'civil_code' => 'Código Civil chileno',
                'commercial_code' => 'Código de Comercio chileno',
                'data_protection_law' => 'Ley 19.628 sobre Protección de la Vida Privada',
                'transfer_tax' => 'Impuesto de Timbres y Estampillas / Derechos notariales',
                'jurisdiction_clause' => 'Tribunales de Justicia de :city (Chile)',
                'warranty_hidden_defects' => 'arts. 1857-1875 del Código Civil',
                'consumer_warranty_years' => 'garantía legal de seis meses (Ley 19.496)',
                'eu' => false,
            ],
            'PE' => [
                'code' => 'PE',
                'name' => 'Perú',
                'currency' => 'PEN',
                'tax_name' => 'IGV',
                'tax_id_label' => 'RUC / DNI',
                'company_tax_id_label' => 'RUC',
                'consumer_law' => 'Código de Protección y Defensa del Consumidor (Ley 29571)',
                'civil_code' => 'Código Civil peruano',
                'commercial_code' => 'Código de Comercio peruano',
                'data_protection_law' => 'Ley 29733 de Protección de Datos Personales',
                'transfer_tax' => 'Impuesto de Alcabala (según municipio)',
                'jurisdiction_clause' => 'Juzgados de :city (Perú)',
                'warranty_hidden_defects' => 'arts. 1507-1521 del Código Civil',
                'consumer_warranty_years' => 'garantía según el Código de Protección al Consumidor',
                'eu' => false,
            ],
            'UY' => [
                'code' => 'UY',
                'name' => 'Uruguay',
                'currency' => 'UYU',
                'tax_name' => 'IVA',
                'tax_id_label' => 'RUT / cédula',
                'company_tax_id_label' => 'RUT',
                'consumer_law' => 'Ley 17.250 de Relaciones de Consumo',
                'civil_code' => 'Código Civil uruguayo',
                'commercial_code' => 'Código de Comercio uruguayo',
                'data_protection_law' => 'Ley 18.331 de Protección de Datos Personales',
                'transfer_tax' => 'Impuesto a las Transmisiones Patrimoniales (ITP) / IMESI según rubro',
                'jurisdiction_clause' => 'Tribunales de :city (Uruguay)',
                'warranty_hidden_defects' => 'arts. 1697-1726 del Código Civil',
                'consumer_warranty_years' => 'Ley 17.250 de Relaciones de Consumo',
                'eu' => false,
            ],
            default => throw new \InvalidArgumentException("Jurisdicción no soportada: {$country}"),
        };
    }

    public function isSupported(string $country): bool
    {
        return in_array(strtoupper($country), self::COUNTRIES, true);
    }

    /**
     * @return array<int, array{code: string, name: string, currency: string, tax_name: string}>
     */
    public function supported(): array
    {
        return array_map(
            fn (string $code) => [
                'code' => $code,
                'name' => $this->config($code)['name'],
                'currency' => $this->config($code)['currency'],
                'tax_name' => $this->config($code)['tax_name'],
            ],
            self::COUNTRIES
        );
    }
}
