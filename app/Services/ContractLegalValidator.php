<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Party;

/**
 * Validates a contract against Spanish and European legal requirements.
 *
 * Returns an array of issues, each one being:
 *   ['level' => 'error'|'warning', 'field' => string, 'message' => string]
 *
 * Rules applied:
 *  - Tax ID correctness (Spanish NIF/CIF/NIE or EU VAT).
 *  - Intracomunitario B2B: both parties must hold valid EU VAT numbers and be
 *    registered for VAT (inversión del sujeto pasivo).
 *  - B2C: mandatory consumer protection wording (garantía, desistimiento,
 *    jurisdicción imperativa del consumidor) — Directive 2011/83/EU + RDL 1/2007.
 *  - C2C: ITP (RDL 1/1993) informativo.
 *  - Unfair terms (Directive 93/13/CEE): warning about disproportionate clauses.
 *  - Jurisdiction clause validity (Reglamento Bruselas I bis 1215/2012).
 *  - Economic consistency (total = price + tax, quantity > 0).
 */
class ContractLegalValidator
{
    public function __construct(
        private readonly SpanishTaxIdValidator $spanishTaxId,
        private readonly EuVatValidator $euVat,
        private readonly LatinAmericanTaxIdValidator $latamTaxId,
        private readonly CountryLegalConfig $countryConfig,
        private readonly TransactionResolver $resolver,
    ) {}

    /**
     * @return array<int, array{level: string, field: string, message: string}>
     */
    public function validate(Contract $contract): array
    {
        $issues = [];

        $seller = $contract->seller();
        $buyer = $contract->buyer();

        if (! $seller || ! $buyer) {
            $issues[] = ['level' => 'error', 'field' => 'parties', 'message' => 'El contrato debe contar con un vendedor y un comprador.'];

            return $issues;
        }

        $resolution = $this->resolver->resolve($seller, $buyer);

        $this->validateTaxIds($issues, $seller);
        $this->validateTaxIds($issues, $buyer);
        $this->validateEconomicConsistency($issues, $contract);
        $this->validateIntracomunitario($issues, $resolution, $seller, $buyer);
        $this->validateConsumerProtection($issues, $resolution, $contract);
        $this->validateC2c($issues, $resolution, $contract);
        $this->validateJurisdiction($issues, $resolution, $seller, $buyer);
        $this->validateInmuebles($issues, $contract);
        $this->validateSpecialClauses($issues, $contract);

        return $issues;
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateTaxIds(array &$issues, Party $party): void
    {
        $country = strtoupper($party->tax_id_country);

        if ($country === 'ES') {
            if (! $this->spanishTaxId->isValid($party->tax_id)) {
                $issues[] = [
                    'level' => 'error',
                    'field' => "party.{$party->role}.tax_id",
                    'message' => "El NIF/CIF/NIE '{$party->tax_id}' de la parte «{$party->displayName()}» no es válido.",
                ];
            }
            if ($party->isProfessional() && ! in_array(strtoupper($party->tax_id)[0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'N', 'P', 'Q', 'R', 'S', 'U', 'V', 'W'], true)) {
                $issues[] = [
                    'level' => 'warning',
                    'field' => "party.{$party->role}.tax_id",
                    'message' => "La parte «{$party->displayName()}» figura como profesional pero su NIF no es un CIF. Si se trata de un autónomo, el NIF personal es correcto.",
                ];
            }

            return;
        }

        if (! $this->euVat->isValidCountry($country)) {
            // Latin American jurisdictions supported by their own validator.
            if ($this->countryConfig->isSupported($country)) {
                if (! $this->latamTaxId->isValid($country, $party->tax_id)) {
                    $issues[] = [
                        'level' => 'error',
                        'field' => "party.{$party->role}.tax_id",
                        'message' => "El documento fiscal '{$party->tax_id}' ({$country}) no tiene un formato válido.",
                    ];
                }

                return;
            }

            $issues[] = [
                'level' => 'error',
                'field' => "party.{$party->role}.tax_id_country",
                'message' => "El país '{$country}' no está soportado aún. Jurisdicciones soportadas: España y América Latina (AR, MX, CO, CL, PE, UY).",
            ];

            return;
        }

        $result = $this->euVat->validate($country, $party->tax_id);

        if (! $result['valid']) {
            $issues[] = [
                'level' => 'error',
                'field' => "party.{$party->role}.tax_id",
                'message' => "El número de IVA '{$party->tax_id}' ({$country}) no es válido.",
            ];
        } elseif ($result['checked_via_vies'] === false) {
            $issues[] = [
                'level' => 'warning',
                'field' => "party.{$party->role}.tax_id",
                'message' => "El número de IVA '{$party->tax_id}' ({$country}) tiene formato correcto, pero no ha podido verificarse en VIES. Verifíquelo manualmente.",
            ];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateEconomicConsistency(array &$issues, Contract $contract): void
    {
        if ($contract->quantity < 1) {
            $issues[] = ['level' => 'error', 'field' => 'quantity', 'message' => 'La cantidad debe ser al menos 1.'];
        }
        if ($contract->price_amount <= 0) {
            $issues[] = ['level' => 'error', 'field' => 'price_amount', 'message' => 'El precio debe ser mayor que cero.'];
        }
        if (abs(((float) $contract->total_amount) - ((float) $contract->price_amount + (float) $contract->tax_amount)) > 0.005) {
            $issues[] = ['level' => 'error', 'field' => 'total_amount', 'message' => 'El importe total debe ser igual a precio + impuestos.'];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateIntracomunitario(array &$issues, array $resolution, Party $seller, Party $buyer): void
    {
        if (! $resolution['intracomunitario_b2b']) {
            return;
        }

        foreach ([$seller, $buyer] as $party) {
            if (! $party->registered_vat) {
                $issues[] = [
                    'level' => 'error',
                    'field' => "party.{$party->role}.registered_vat",
                    'message' => "Operación intracomunitaria B2B: la parte «{$party->displayName()}» debe estar inscrita en el registro de operadores intracomunitarios (ROI) / VIES y marcarse como sujeto pasivo del IVA.",
                ];
            }
            if (strtoupper($party->tax_id_country) === 'ES' && ! str_starts_with(strtoupper($party->tax_id), 'ES')) {
                $issues[] = [
                    'level' => 'warning',
                    'field' => "party.{$party->role}.tax_id",
                    'message' => 'Para operaciones intracomunitarias conviene anotar el número con prefijo de país (ES…).',
                ];
            }
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateConsumerProtection(array &$issues, array $resolution, Contract $contract): void
    {
        $isB2c = $resolution['transaction_type'] === 'b2c';
        $isDistance = $isB2c && ($contract->contract_type === 'servicios' || $contract->contract_type === 'bienes_muebles');

        if (! $isB2c) {
            return;
        }

        $clauses = $contract->clauses ?? [];
        $clauseTitles = array_map(fn ($c) => strtolower((string) $c['title']), $clauses);

        if (! $this->hasKeyword($clauseTitles, ['desistimiento', 'desistir', 'arrepentimiento'])) {
            $level = $isDistance ? 'error' : 'warning';
            $issues[] = [
                'level' => $level,
                'field' => 'clauses',
                'message' => $isDistance
                    ? 'Venta a consumidor a distancia/fuera de establecimiento: es obligatoria la cláusula de desistimiento de 14 días (art. 102 y ss. RDL 1/2007).'
                    : 'Se recomienda incluir cláusula de desistimiento cuando proceda (art. 102 y ss. RDL 1/2007).',
            ];
        }

        if (! $this->hasKeyword($clauseTitles, ['garant', 'saneamiento'])) {
            $issues[] = [
                'level' => 'warning',
                'field' => 'clauses',
                'message' => 'Se recomienda incluir la cláusula de garantía legal (2 años bienes, 3 años desde 2022) y saneamiento por vicios ocultos (arts. 1484 y ss. CC).',
            ];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateC2c(array &$issues, array $resolution, Contract $contract): void
    {
        if ($resolution['transaction_type'] !== 'c2c') {
            return;
        }

        if ($contract->contract_type === 'inmuebles') {
            $issues[] = [
                'level' => 'warning',
                'field' => 'jurisdiction',
                'message' => 'Transmisión de inmueble entre particulares: sujeta a ITP (RDL 1/1993, tipo autonómico). Liquidación en la oficina liquidadora del domicilio del inmueble dentro de los 30 días hábiles siguientes a la firma.',
            ];
        } else {
            $issues[] = [
                'level' => 'warning',
                'field' => 'jurisdiction',
                'message' => 'Transmisión entre particulares: no sujeta a IVA; puede quedar sujeta a ITP (RDL 1/1993) que corresponde al adquirente. La garantía por vicios ocultos se rige por los arts. 1484-1499 del Código Civil.',
            ];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateJurisdiction(array &$issues, array $resolution, Party $seller, Party $buyer): void
    {
        $crossBorder = $resolution['jurisdiction'] !== 'nacional';
        $hasConsumer = $resolution['transaction_type'] === 'b2c';

        if ($crossBorder && $hasConsumer) {
            $issues[] = [
                'level' => 'warning',
                'field' => 'clauses',
                'message' => 'En B2C transfronterizo la jurisdicción pactada no puede privar al consumidor de la protección de los tribunales de su domicilio (art. 17-18 Reglamento 1215/2012 «Bruselas I bis»).',
            ];
        }

        if (! $crossBorder && ! $hasConsumer) {
            $issues[] = [
                'level' => 'warning',
                'field' => 'clauses',
                'message' => 'Se recomienda incluir cláusula de jurisdicción/sometimiento expreso a tribunales y de ley aplicable (arts. 1255 y 1256 CC).',
            ];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateInmuebles(array &$issues, Contract $contract): void
    {
        if ($contract->contract_type !== 'inmuebles') {
            return;
        }

        if (blank($contract->object_type)) {
            $issues[] = ['level' => 'error', 'field' => 'object_type', 'message' => 'Para inmuebles debe indicarse el tipo (vivienda, local, nave, terreno…).'];
        }
        if (blank($contract->delivery_terms)) {
            $issues[] = ['level' => 'warning', 'field' => 'delivery_terms', 'message' => 'Se recomienda fijar plazo y forma de entrega/ocupación, cargas y gastos (notaría, registro, plusvalía municipal).'];
        }
        if ($contract->price_amount >= 1000000) {
            $issues[] = ['level' => 'warning', 'field' => 'price_amount', 'message' => 'Importe elevado: se recomienda escritura pública ante notario para la inscripción registral (art. 1280 CC).'];
        }
    }

    /**
     * @param  array<int, array{level: string, field: string, message: string}>  $issues
     */
    private function validateSpecialClauses(array &$issues, Contract $contract): void
    {
        if (blank($contract->special_clauses)) {
            return;
        }

        $text = strtolower($contract->special_clauses);

        foreach (['pena de 100', 'sin responsabilidad', 'no responde', 'renuncia a toda garantía', 'exclusión de toda responsabilidad'] as $flag) {
            if (str_contains($text, $flag)) {
                $issues[] = [
                    'level' => 'warning',
                    'field' => 'special_clauses',
                    'message' => "La cláusula especial «{$flag}» puede resultar abusiva y nula en B2C (Directiva 93/13/CEE, art. 82 y ss. RDL 1/2007).",
                ];
            }
        }
    }

    /**
     * @param  array<int, string>  $titles
     */
    private function hasKeyword(array $titles, array $keywords): bool
    {
        foreach ($titles as $title) {
            foreach ($keywords as $keyword) {
                if (str_contains($title, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }
}
