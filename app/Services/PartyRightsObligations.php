<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Party;

/**
 * Generates, in plain language, the main rights and obligations of each party
 * for a given contract, adapted to the regime (B2B/B2C/C2C/C2B) and to the
 * applicable jurisdiction (Spain / Latin America).
 *
 * This is the "both parties know their rights and obligations" surface shown
 * during review and signing, and embedded in the final document.
 */
class PartyRightsObligations
{
    public function __construct(private readonly CountryLegalConfig $countryConfig) {}

    /**
     * @return array{rights: array<int, string>, obligations: array<int, string>, legal_refs: array<int, string>}
     */
    public function for(Contract $contract, Party $party): array
    {
        $isSeller = $party->role === 'vendedor';
        $cfg = $this->countryConfig->config($contract->applicable_law);
        $regime = $contract->transaction_type;

        $rights = $isSeller ? $this->sellerRights($contract, $cfg) : $this->buyerRights($contract, $cfg);
        $obligations = $isSeller ? $this->sellerObligations($contract, $cfg) : $this->buyerObligations($contract, $cfg);

        if ($regime === 'b2c' && ! $isSeller) {
            $rights = array_merge($rights, $this->consumerRights($cfg));
        }
        if ($regime === 'b2b') {
            $rights[] = 'Derecho al cobro/pago del precio y a la protección frente a la morosidad comercial.';
            $obligations[] = 'Cumplir las obligaciones mercantiles y de facturación de la jurisdicción aplicable.';
        }
        if ($regime === 'c2c') {
            $rights[] = 'Derecho a exigir la entrega del bien libre de cargas y gravámenes no declarados.';
            $obligations[] = 'Asumir los impuestos y trámites que correspondan según la normativa local (transmisiones patrimoniales, registro o transferencia).';
        }
        if ($regime === 'c2b' && $isSeller) {
            $rights[] = 'Derecho a percibir el precio íntegro acordado y a que el comprador profesional verifique el bien adquirido.';
            $obligations[] = 'Entregar el bien en el estado declarado y responder por los vicios ocultos (saneamiento).';
        }

        $legalRefs = [
            "Ley aplicable: {$cfg['name']} ({$cfg['civil_code']}).",
            "Defensa del consumidor (si procede): {$cfg['consumer_law']}.",
            "Protección de datos: {$cfg['data_protection_law']}.",
        ];

        return [
            'rights' => array_values(array_unique($rights)),
            'obligations' => array_values(array_unique($obligations)),
            'legal_refs' => $legalRefs,
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function sellerRights(Contract $contract, array $cfg): array
    {
        $price = number_format((float) $contract->total_amount, 2, ',', '.').' '.$contract->currency;

        if ($contract->contract_type === 'arras') {
            return [
                "Derecho a percibir el importe pactado de las arras o señal ({$price}) como reserva del inmueble.",
                'Derecho a retener las arras en caso de desistimiento o incumplimiento del comprador (art. 1454 Código Civil).',
                'Derecho a exigir la comparecencia ante Notario y el abono del resto del precio en la fecha acordada.',
            ];
        }

        return [
            "Derecho a recibir el precio pactado ({$price}) en la forma y plazos convenidos.",
            'Derecho a que el comprador reciba el bien y colabore en los trámites formales (entrega, registro o transferencia).',
            'Derecho a reclamar los gastos o daños que la falta de colaboración del comprador le cause.',
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function sellerObligations(Contract $contract, array $cfg): array
    {
        if ($contract->contract_type === 'arras') {
            return [
                'Reservar el inmueble en favor del comprador y abstenerse de ofrecerlo o gravarlo a terceros.',
                'Devolver las arras duplicadas en caso de desistir de la venta (art. 1454 Código Civil).',
                'Aportar nota simple, recibo del IBI, certificado de comunidad y cancelar cargas previas a la escritura.',
                'Comparecer ante el Notario designado para otorgar la escritura pública de compraventa en el plazo acordado.',
            ];
        }

        return [
            'Entregar el bien objeto del contrato en el estado y plazo pactados.',
            'Entregar el bien libre de cargas o gravámenes no declarados y garantizar la propiedad que transmite.',
            "Responder del saneamiento por vicios ocultos ({$cfg['warranty_hidden_defects']}).",
            'Entregar los documentos y comprobantes necesarios para la operación (factura, ficha, certificados).',
            'Cumplir con las obligaciones fiscales que le correspondan (impuestos a la venta si actúa como profesional).',
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function buyerRights(Contract $contract, array $cfg): array
    {
        $price = number_format((float) $contract->total_amount, 2, ',', '.').' '.$contract->currency;

        if ($contract->contract_type === 'arras') {
            return [
                'Derecho a la reserva exclusiva del inmueble hasta la fecha límite pactada.',
                'Derecho a percibir el doble de las arras entregadas si el vendedor desiste de la venta (art. 1454 Código Civil).',
                'Derecho a designar la Notaría para el otorgamiento de la escritura pública de compraventa.',
                'Derecho a recibir el inmueble completamente libre de cargas, hipotecas y ocupantes.',
            ];
        }

        return [
            "Derecho a recibir el bien en el estado descrito y dentro del plazo acordado por el precio de {$price}.",
            'Derecho a recibir el bien libre de cargas y gravámenes no declarados.',
            "Derecho al saneamiento por vicios ocultos ({$cfg['warranty_hidden_defects']}).",
            'Derecho a que el vendedor colabore en los trámites de entrega, registro o transferencia.',
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function buyerObligations(Contract $contract, array $cfg): array
    {
        $price = number_format((float) $contract->total_amount, 2, ',', '.').' '.$contract->currency;

        if ($contract->contract_type === 'arras') {
            return [
                "Entregar el importe acordado de las arras ({$price}) en la forma convenida a cuenta del precio final.",
                'Perder la cantidad entregada en concepto de arras en caso de desistir unilateralmente de la compra.',
                'Comparecer ante Notario y abonar el importe restante del precio en la fecha pactada.',
            ];
        }

        return [
            "Abonar el precio pactado ({$price}) en la forma y plazos convenidos.",
            'Recibir el bien en la forma pactada y colaborar en los trámites formales.',
            'Asumir los impuestos y gastos que la normativa local atribuye al adquirente (transmisiones patrimoniales, registro, transferencia de titularidad).',
            'Verificar el estado del bien antes de su recepción definitiva.',
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function consumerRights(array $cfg): array
    {
        return [
            'Derecho a la información previa, clara y suficiente sobre el bien o servicio.',
            'Derecho a la garantía de conformidad y, cuando proceda, al desistimiento dentro del plazo legal.',
            "Derecho a que las cláusulas no sean abusivas ({$cfg['consumer_law']}).",
            'Derecho a acudir a los tribunales de su domicilio cuando la ley lo reconozca.',
        ];
    }
}
