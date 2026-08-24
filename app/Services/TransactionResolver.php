<?php

namespace App\Services;

use App\Models\Party;

/**
 * Resolves the transaction regime from the two counterparties.
 *
 * - transaction_type: b2b | b2c | c2c | c2b
 * - jurisdiction: nacional (both in Spain) | intracomunitario (both in the EU,
 *   different countries) | internacional (any party outside the EU)
 */
class TransactionResolver
{
    public function __construct(private readonly EuVatValidator $vatValidator) {}

    /**
     * @return array{transaction_type: string, jurisdiction: string, intracomunitario_b2b: bool}
     */
    public function resolve(Party $seller, Party $buyer): array
    {
        $transactionType = $this->transactionType($seller, $buyer);
        $jurisdiction = $this->jurisdiction($seller, $buyer);

        return [
            'transaction_type' => $transactionType,
            'jurisdiction' => $jurisdiction,
            'intracomunitario_b2b' => $transactionType === 'b2b' && $jurisdiction === 'intracomunitario',
        ];
    }

    public function transactionType(Party $seller, Party $buyer): string
    {
        $sellerProfessional = $seller->isProfessional();
        $buyerProfessional = $buyer->isProfessional();

        return match (true) {
            $sellerProfessional && $buyerProfessional => 'b2b',
            $sellerProfessional && ! $buyerProfessional => 'b2c',
            ! $sellerProfessional && ! $buyerProfessional => 'c2c',
            default => 'c2b',
        };
    }

    public function jurisdiction(Party $seller, Party $buyer): string
    {
        $sellerCountry = strtoupper($seller->country);
        $buyerCountry = strtoupper($buyer->country);

        if ($sellerCountry === $buyerCountry) {
            return 'nacional';
        }

        if (
            $this->vatValidator->isValidCountry($sellerCountry)
            && $this->vatValidator->isValidCountry($buyerCountry)
        ) {
            return 'intracomunitario';
        }

        return 'internacional';
    }

    /**
     * VAT treatment notes for the legal document, adapted to the applicable
     * jurisdiction (Spain or Latin America).
     *
     * @param  array{transaction_type: string, jurisdiction: string, intracomunitario_b2b: bool}  $resolution
     */
    public function vatTreatmentNotes(array $resolution, string $applicableLaw = 'ES'): string
    {
        $country = strtoupper($applicableLaw);
        $config = app(CountryLegalConfig::class)->config($country);
        $tax = $config['tax_name'];
        $consumer = $config['consumer_law'];
        $transferTax = $config['transfer_tax'];

        return match (true) {
            $resolution['intracomunitario_b2b'] => 'Operación intracomunitaria sujeta a IVA con inversión del sujeto pasivo (art. 84.Uno.2º Ley 37/1992 del IVA). El comprador declara la operación en su declaración periódica. IVA: 0%.',
            $resolution['transaction_type'] === 'b2b' && $resolution['jurisdiction'] === 'nacional' => "Operación sujeta a {$tax}. El vendedor repercute el impuesto correspondiente y emite factura conforme a la normativa de la jurisdicción ({$config['name']}).",
            $resolution['transaction_type'] === 'b2b' && $resolution['jurisdiction'] === 'internacional' => "Operación internacional: {$tax} / impuestos indirectos según el país de origen y destino. El comprador es responsable de las obligaciones aduaneras y fiscales de su país.",
            $resolution['transaction_type'] === 'b2c' => "Venta a consumidor final: el vendedor repercute el {$tax} aplicable en {$config['name']}. Aplicable {$consumer}: garantía legal y, cuando proceda, derecho de desistimiento.",
            $resolution['transaction_type'] === 'c2c' => "Transmisión entre particulares: no sujeta a {$tax}. Puede quedar sujeta a impuestos locales ({$transferTax}) que corresponden al adquirente según la normativa de {$config['name']}.",
            $resolution['transaction_type'] === 'c2b' => "Venta de un particular a un profesional: no sujeta a {$tax} cuando el vendedor no es empresario. Pueden aplicar retenciones o regímenes especiales de bienes usados según la normativa local.",
            default => 'Operación sujeta a la normativa fiscal aplicable en cada caso.',
        };
    }
}
