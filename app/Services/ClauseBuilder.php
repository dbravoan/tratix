<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Party;

/**
 * Builds the body clauses of a buy/sell contract in Spanish, adapting the
 * wording to the transaction type (B2B / B2C / C2C / C2B) and jurisdiction
 * (nacional / intracomunitario / internacional).
 *
 * Legal basis: Código Civil, Código de Comercio, Ley 37/1992 del IVA,
 * RDL 1/2007 (LGDCU), RDL 1/1993 (ITP), Reglamento 1215/2012 (Bruselas I bis),
 * Directiva 93/13/CEE, Reglamento UE 2018/302 (geo-bloqueo), RDL 3/2018 (LOPD).
 */
class ClauseBuilder
{
    public function __construct(
        private readonly CountryLegalConfig $countryConfig,
        private readonly PartyRightsObligations $rightsObligations,
    ) {}

    public const TYPE_LABELS = [
        'bienes_muebles' => 'compraventa de bienes muebles',
        'inmuebles' => 'compraventa de inmueble',
        'vehiculos' => 'compraventa de vehículo',
        'servicios' => 'prestación de servicios',
        'internacional' => 'compraventa internacional de mercancías',
        'alquiler' => 'arrendamiento / alquiler',
        'prestamo' => 'préstamo',
        'cesion_derechos' => 'cesión de derechos',
        'nda' => 'acuerdo de confidencialidad',
        'arras' => 'contrato de arras (reserva de inmueble)',
    ];

    public const TRANSACTION_LABELS = [
        'b2b' => 'entre profesionales',
        'b2c' => 'venta a consumidor final',
        'c2c' => 'entre particulares',
        'c2b' => 'compra por profesional a particular',
    ];

    /**
     * @return array<int, array{key: string, title: string, body: string}>
     */
    public function build(Contract $contract, Party $seller, Party $buyer, array $resolution): array
    {
        $isSaleType = in_array($contract->contract_type, ['bienes_muebles', 'inmuebles', 'vehiculos', 'servicios', 'internacional'], true);

        $clauses = [];
        $clauses[] = $this->keyed('partes', $this->partiesClause($seller, $buyer));
        $clauses[] = $this->keyed('objeto', $this->objectClause($contract));
        $clauses[] = $this->keyed('precio', $this->priceClause($contract, $resolution));

        if ($isSaleType) {
            $clauses[] = $this->keyed('entrega', $this->deliveryClause($contract, $resolution));
            $clauses[] = $this->keyed('garantias', $this->warrantyClause($contract, $resolution));
            $clauses[] = $this->keyed('impuestos', $this->taxClause($contract, $resolution));
        }

        if ($resolution['transaction_type'] === 'b2c') {
            $clauses[] = $this->keyed('derechos_consumidor', $this->consumerRightsClause($contract));
        }

        foreach ($this->typeSpecificClauses($contract) as $key => $clause) {
            $clauses[] = $this->keyed($key, $clause);
        }

        $clauses[] = $this->keyed('jurisdiccion', $this->jurisdictionClause($contract, $resolution, $seller, $buyer));
        $clauses[] = $this->keyed('derechos_obligaciones', $this->rightsObligationsClause($contract, $seller, $buyer));
        $clauses[] = $this->keyed('proteccion_datos', $this->dataProtectionClause($contract));
        $clauses[] = $this->keyed('clausulas_especiales', $this->specialClauses($contract));
        $clauses[] = $this->keyed('firma', $this->signatureClause($seller, $buyer));

        return $clauses;
    }

    /**
     * @param  array{title: string, body: string}  $clause
     * @return array{key: string, title: string, body: string}
     */
    private function keyed(string $key, array $clause): array
    {
        $clause['key'] = $key;

        return $clause;
    }

    /**
     * @return array{title: string, body: string}
     */
    private function partiesClause(Party $seller, Party $buyer): array
    {
        $sellerData = $this->partyIdentity($seller);
        $buyerData = $this->partyIdentity($buyer);

        return [
            'title' => 'Partes intervinientes',
            'body' => 'De una parte, como VENDEDOR, '.$sellerData.'. '
                .'Y de otra parte, como COMPRADOR, '.$buyerData.'. '
                .'Ambas partes, con capacidad legal para contratar y otorgar el presente documento, '
                .'intervienen libremente y se reconocen mutuamente dicha capacidad.',
        ];
    }

    private function partyIdentity(Party $party): string
    {
        $name = $party->displayName();
        $taxId = strtoupper($party->tax_id);
        $taxId = strtoupper($party->tax_id_country) !== 'ES'
            ? $party->tax_id_country.'-'.$taxId
            : $taxId;

        $ident = $name.' ('.$party->role.'), con NIF '.$taxId.', mayor de edad';

        if ($party->isProfessional()) {
            $activity = blank($party->activity) ? 'actividad profesional o empresarial' : $party->activity;
            $ident .= ', que actúa en su condición de empresario o profesional ('.$activity.')';
        } else {
            $ident .= ', que actúa en su propio nombre y derecho como particular';
        }

        $ident .= ', con domicilio en '.trim($party->address.', '.$party->postal_code.', '.$party->city).', '.strtoupper($party->country).'.';

        return $ident;
    }

    /**
     * @return array{title: string, body: string}
     */
    private function objectClause(Contract $contract): array
    {
        if ($contract->contract_type === 'alquiler') {
            return [
                'title' => 'Objeto del contrato',
                'body' => 'La parte arrendadora cede el uso y disfrute del bien o inmueble descrito a continuación, '
                    .'por tiempo determinado y mediante el pago de una renta: '.$contract->object_description
                    .'. El objeto se entrega en el estado que se describe y deberá devolverse en condiciones similares, salvo el desgaste por uso normal.',
            ];
        }

        if ($contract->contract_type === 'prestamo') {
            return [
                'title' => 'Objeto del contrato',
                'body' => 'La parte prestamista entrega en préstamo al prestatario la cantidad de '
                    .number_format((float) $contract->price_amount, 2, ',', '.').' '.$contract->currency
                    .', que este se obliga a devolver en el plazo y condiciones pactados. Descripción de la operación: '.$contract->object_description,
            ];
        }

        if ($contract->contract_type === 'cesion_derechos') {
            return [
                'title' => 'Objeto del contrato',
                'body' => 'La parte cedente transmite a favor de la parte cesionaria los derechos que se describen a continuación, '
                    .'en los términos y con el alcance pactados: '.$contract->object_description,
            ];
        }

        if ($contract->contract_type === 'nda') {
            return [
                'title' => 'Objeto del contrato',
                'body' => 'Las partes acuerdan mantener la confidencialidad de la información que se intercambien con motivo de la relación descrita: '
                    .$contract->object_description,
            ];
        }

        $qty = $contract->quantity > 1 ? "Se transmite la cantidad de {$contract->quantity} unidades del siguiente objeto." : 'El objeto de la transmisión es el siguiente:';

        return [
            'title' => 'Objeto del contrato',
            'body' => $qty.' '.$contract->object_description
                .' El objeto se vende «como está» en cuanto a su estado y características, salvo lo dispuesto en la cláusula de garantías,'
                .' con todos los derechos, accesorios y, en su caso, cargas declaradas que se indican.'
                .($contract->object_type ? " Tipo de bien: {$contract->object_type}." : ''),
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function priceClause(Contract $contract, array $resolution): array
    {
        if ($contract->contract_type === 'alquiler') {
            return [
                'title' => 'Renta y forma de pago',
                'body' => 'La renta mensual convenida asciende a '
                    .number_format((float) $contract->price_amount, 2, ',', '.').' '.$contract->currency
                    .'. '.($contract->payment_terms ?: 'Se abonará por mensualidades anticipadas mediante transferencia bancaria.')
                    .' La renta se actualizará según lo pactado y conforme a la legislación aplicable al arrendamiento.',
            ];
        }

        if ($contract->contract_type === 'prestamo') {
            return [
                'title' => 'Importe y devolución',
                'body' => 'El importe prestado asciende a '
                    .number_format((float) $contract->price_amount, 2, ',', '.').' '.$contract->currency
                    .'. '.($contract->payment_terms ?: 'Se devolverá en la fecha o plazos pactados, sin intereses salvo que se indique lo contrario.')
                    .' Esta operación no genera obligaciones de IVA al tratarse de un préstamo entre las partes.',
            ];
        }

        if ($contract->contract_type === 'nda') {
            return [
                'title' => 'Contraprestación',
                'body' => 'Este acuerdo de confidencialidad no implica contraprestación económica por sí mismo, salvo lo que expresamente se pacte por escrito.',
            ];
        }

        $price = number_format((float) $contract->price_amount, 2, ',', '.').' '.$contract->currency;
        $total = number_format((float) $contract->total_amount, 2, ',', '.').' '.$contract->currency;

        $taxNote = match ($resolution['transaction_type']) {
            'b2b' => 'El precio se entiende sin IVA, que se repercutirá conforme a la cláusula fiscal. ',
            'b2c' => 'El precio incluye el IVA vigente aplicable. ',
            'c2c' => 'El precio no está sujeto a IVA al tratarse de una transmisión entre particulares. ',
            'c2b' => 'El precio se pacta sin IVA, al no estar sujeta la operación, salvo que el vendedor actúe como empresario. ',
        };

        $payment = blank($contract->payment_terms)
            ? 'El pago se realizará íntegro en el momento de la firma del presente contrato, mediante transferencia bancaria u otro medio de pago fehaciente.'
            : $contract->payment_terms;

        return [
            'title' => 'Precio y forma de pago',
            'body' => "El precio total convenido asciende a {$total} (importe bruto {$price}). "
                .$taxNote
                ."Forma de pago: {$payment}",
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function deliveryClause(Contract $contract, array $resolution): array
    {
        $delivery = blank($contract->delivery_terms)
            ? 'La entrega del objeto se realizará en el plazo pactado por las partes o, en su defecto, de forma inmediata tras la firma del contrato.'
            : $contract->delivery_terms;

        return [
            'title' => 'Entrega y plazo',
            'body' => $delivery
                .($resolution['jurisdiction'] !== 'nacional'
                    ? ' Tratándose de una operación transfronteriza, los plazos de entrega se computan según los usos comerciales internacionales (Incoterms pactados si los hubiera).'
                    : ''),
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function warrantyClause(Contract $contract, array $resolution): array
    {
        $custom = blank($contract->warranties) ? null : $contract->warranties;

        $bodies = [
            'b2c' => 'El vendedor responde de las faltas de conformidad del bien o servicio conforme a la Ley General para la Defensa de los Consumidores y Usuarios '
                .'(RDL 1/2007): garantía legal de dos años (bienes muebles, tres años desde el 1 de enero de 2022 para las entregas posteriores al 31/12/2021), '
                .'con derecho del consumidor a la reparación o sustitución, y en su defecto a la rebaja del precio o resolución. '
                .'Quedan a salvo las acciones por vicios ocultos del Código Civil (arts. 1484 y ss.).',
            'c2c' => 'El vendedor entrega el bien libre de cargas y gravámenes no declarados y responde de los vicios o defectos ocultos que lo hagan impropio para su uso '
                .'conforme a los arts. 1484 a 1499 del Código Civil. No resulta aplicable la garantía de la LGDCU al tratarse de una transmisión entre particulares.',
            'c2b' => 'El vendedor, particular, responde del saneamiento por vicios ocultos (arts. 1484 y ss. CC). El comprador profesional asume la verificación del estado del bien, '
                .'pudiendo aplicarse el régimen especial de bienes usados (REBU) en su posterior venta.',
            'b2b' => 'El vendedor responde del saneamiento por vicios ocultos (arts. 1484 y ss. CC) y de las faltas de conformidad conforme a la normativa mercantil '
                .'(Código de Comercio, arts. 325 y ss. para la compraventa mercantil). Las partes podrán pactar términos de garantía específicos.',
        ];

        $body = $bodies[$resolution['transaction_type']];
        if ($custom) {
            $body .= ' Garantías adicionales pactadas: '.$custom.'.';
        }

        return ['title' => 'Garantías y saneamiento', 'body' => $body];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function taxClause(Contract $contract, array $resolution): array
    {
        $notes = $this->resolverNote($contract, $resolution);

        return [
            'title' => 'Impuestos y gastos',
            'body' => $notes,
        ];
    }

    private function resolverNote(Contract $contract, array $resolution): string
    {
        $cfg = $this->countryConfig->config($contract->applicableLaw());
        $tax = $cfg['tax_name'];

        return match (true) {
            $resolution['intracomunitario_b2b'] => 'Operación intracomunitaria de bienes exenta de IVA en origen con inversión del sujeto pasivo '
                .'(art. 84.Uno.2º Ley 37/1992). El comprador, sujeto pasivo del IVA en su Estado miembro, declarará la operación en su declaración periódica '
                .'y la consignará en la declaración recapitulativa de su país. Ambas partes deben disponer de número de IVA válido en el sistema VIES.',
            $resolution['transaction_type'] === 'b2b' => "Operación sujeta a {$tax}. El vendedor repercute el impuesto aplicable y emite factura "
                ."conforme a la normativa de facturación de {$cfg['name']}.",
            $resolution['transaction_type'] === 'b2c' => "Operación sujeta a {$tax}. El precio incluye el impuesto, que el vendedor ingresa. "
                ."Aplicable {$cfg['consumer_law']}.",
            $resolution['transaction_type'] === 'c2c' => "Transmisión entre particulares no sujeta a {$tax} ni a tributos sobre la renta habitual. "
                ."El adquirente quedará sujeto a los impuestos locales que correspondan ({$cfg['transfer_tax']}) según la normativa de {$cfg['name']}.",
            $resolution['transaction_type'] === 'c2b' => "Adquisición de un particular: no sujeta a {$tax} cuando el vendedor no es empresario. "
                .'Pueden aplicar retenciones o regímenes especiales de bienes usados según la normativa local.',
            default => 'Impuestos conforme a la normativa aplicable.',
        };
    }

    /**
     * @return array{title: string, body: string}
     */
    private function consumerRightsClause(Contract $contract): array
    {
        $cfg = $this->countryConfig->config($contract->applicableLaw());
        $isDistance = in_array($contract->contract_type, ['servicios', 'bienes_muebles'], true);

        $withdrawal = $isDistance && $contract->applicableLaw() === 'ES'
            ? ' El consumidor dispone de un plazo de desistimiento de 14 días naturales desde la entrega del bien o la celebración del contrato, '
                .'sin necesidad de justificación (arts. 102 y ss. RDL 1/2007), pudiendo ejercitarlo mediante declaración fehaciente; el vendedor reembolsará '
                .'todos los pagos recibidos en un plazo máximo de 14 días desde que se le comunique el desistimiento. Quedan excluidos los supuestos de los '
                .'arts. 103 y 104 RDL 1/2007 (bienes personalizados, precintados desprecintados, etc.).'
            : '';

        return [
            'title' => 'Derechos del consumidor',
            'body' => 'El comprador actúa como consumidor final y le resultan aplicables, con carácter irrenunciable, los derechos que reconoce '
                .$cfg['consumer_law'].', en particular la garantía de conformidad, el derecho de desistimiento en su caso, '
                .'y la prohibición de cláusulas abusivas.'
                .$withdrawal,
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function jurisdictionClause(Contract $contract, array $resolution, Party $seller, Party $buyer): array
    {
        $cfg = $this->countryConfig->config($contract->applicableLaw());
        $consumer = $resolution['transaction_type'] === 'b2c';
        $crossBorder = $resolution['jurisdiction'] !== 'nacional';
        $forum = str_replace(':city', $buyer->city, $cfg['jurisdiction_clause']);

        if ($consumer) {
            $body = 'Las partes se someten expresamente a los tribunales del domicilio del consumidor, '
                .'sin perjuicio de los fueros imperativos que reconoce la legislación de consumo ('.$cfg['consumer_law'].'). '
                .'A falta de sometimiento, será competente el tribunal del domicilio del consumidor.';
        } elseif ($crossBorder) {
            $body = 'Las partes se someten expresamente a '.$forum.', con renuncia a su propio fuero, salvo norma imperativa aplicable. '
                .'La ley aplicable al contrato es la de '.$cfg['name'].', salvo disposición imperativa en contrario (reglas de conflicto aplicables).';
        } else {
            $body = 'Las partes se someten expresamente a '.$forum.', con renuncia a su propio fuero, salvo norma imperativa aplicable. '
                .'Se aplicará la legislación de '.$cfg['name'].' y, para lo no previsto, el '.$cfg['civil_code'].' y el '.$cfg['commercial_code'].'.';
        }

        return ['title' => 'Ley aplicable y jurisdicción', 'body' => $body];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function rightsObligationsClause(Contract $contract, Party $seller, Party $buyer): array
    {
        $sellerInfo = $this->rightsObligations->for($contract, $seller);
        $buyerInfo = $this->rightsObligations->for($contract, $buyer);

        $format = fn (array $data, string $role) => implode(' ', [
            $role.' — DERECHOS: ',
            implode(' ', array_map(fn ($r) => '• '.$r, $data['rights'])),
            ' OBLIGACIONES: ',
            implode(' ', array_map(fn ($o) => '• '.$o, $data['obligations'])),
        ]);

        $body = $format($sellerInfo, 'VENDEDOR').' '.$format($buyerInfo, 'COMPRADOR')
            .' Referencias legales: '.implode(' ', array_map(fn ($r) => '• '.$r, $sellerInfo['legal_refs']));

        return [
            'title' => 'Derechos y obligaciones de las partes',
            'body' => $body,
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function dataProtectionClause(Contract $contract): array
    {
        $cfg = $this->countryConfig->config($contract->applicableLaw());

        return [
            'title' => 'Protección de datos personales y confidencialidad',
            'body' => 'De conformidad con el Reglamento General de Protección de Datos (RGPD UE 2016/679) y la legislación de protección de datos aplicable en '.$cfg['name'].' ('.$cfg['data_protection_law'].'), '
                .'las partes quedan informadas de que sus datos personales de identificación, contacto y los incorporados al presente contrato serán tratados con la exclusiva finalidad de gestionar, '
                .'formalizar, ejecutar y liquidar la relación contractual dimanante del mismo, así como atender las obligaciones legales y tributarias aplicables. '
                .'La base de legitimación del tratamiento es la ejecución del presente contrato (art. 6.1.b RGPD) y el cumplimiento de obligaciones legales imperativas (art. 6.1.c RGPD). '
                .'Los datos se conservarán durante la vigencia de la relación contractual y, una vez extinguida esta, permanecerán debidamente bloqueados a disposición exclusiva de jueces, tribunales, '
                .'Ministerio Fiscal y Administraciones Públicas competentes durante los plazos legales de prescripción de responsabilidades civiles, mercantiles y tributarias. '
                .'No se prevén cesiones a terceros salvo las legalmente obligatorias o necesarias para el cumplimiento del contrato. '
                .'Las partes se comprometen recíprocamente a guardar estricto secreto profesional y confidencialidad, adoptando las medidas técnicas y organizativas necesarias para garantizar la seguridad de los datos. '
                .'Cada una de las partes podrá ejercer en todo momento sus derechos de acceso, rectificación, supresión, limitación del tratamiento, portabilidad y oposición mediante comunicación escrita '
                .'dirigida a los domicilios consignados en el encabezamiento, así como el derecho a presentar una reclamación ante la autoridad de control competente en materia de protección de datos (en España, la Agencia Española de Protección de Datos - AEPD).',
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    /**
     * @return array<int, array{title: string, body: string}>
     */
    private function typeSpecificClauses(Contract $contract): array
    {
        $delivery = blank($contract->delivery_terms)
            ? 'La entrega se realizará en la fecha o plazo pactados.'
            : $contract->delivery_terms;

        return match ($contract->contract_type) {
            'alquiler' => [
                'arrendamiento' => [
                    'title' => 'Condiciones del arrendamiento',
                    'body' => 'La duración del arrendamiento será la pactada ('.($contract->effective_date ? $contract->effective_date->format('d/m/Y').' en adelante' : 'el plazo convenido').'). '
                        .'La parte arrendataria se obliga a destinar el bien al uso pactado, mantenerlo en buen estado, y a no subarrendar ni ceder el uso sin consentimiento. '
                        .'La parte arrendadora responde del saneamiento y del goce pacífico del bien durante la vigencia.',
                ],
                'entrega' => ['title' => 'Entrega y devolución', 'body' => $delivery.' El bien deberá devolverse a la finalización del arrendamiento en las condiciones pactadas.'],
            ],
            'prestamo' => [
                'obligaciones_prestatario' => [
                    'title' => 'Obligaciones de la parte prestataria',
                    'body' => 'La parte prestataria se obliga a devolver el importe recibido en la fecha o plazos pactados, y a emplearlo para la finalidad declarada. '
                        .'La mora en la devolución podrá generar los intereses pactados y la reclamación de los gastos derivados.',
                ],
            ],
            'cesion_derechos' => [
                'cesion' => [
                    'title' => 'Condiciones de la cesión',
                    'body' => 'La parte cedente transmite los derechos descritos con todos sus accesorios, en la fecha de efectos pactada. '
                        .'La parte cedente responde de la existencia y titularidad de los derechos cedidos, salvo pacto en contrario. '
                        .'Si la cesión fuera onerosa, la contraprestación será la indicada en la cláusula correspondiente.',
                ],
            ],
            'nda' => [
                'confidencialidad' => [
                    'title' => 'Obligación de confidencialidad',
                    'body' => 'Las partes se obligan a no divulgar ni utilizar, salvo para la finalidad pactada, la información confidencial recibida, '
                        .'incluidos datos técnicos, comerciales, financieros y de clientes. La obligación subsiste tras la finalización de la relación. '
                        .'Queda excluida la información que sea pública, ya lo fuera, o que deba revelarse por obligación legal.',
                ],
            ],
            'arras' => [
                'arras_penitenciales' => [
                    'title' => 'Naturaleza de las arras y régimen penitencial (Art. 1454 Código Civil)',
                    'body' => 'La cantidad entregada en concepto de señal o reserva tiene expresamente el carácter de ARRAS PENITENCIALES, de conformidad con lo establecido en el artículo 1454 del Código Civil. '
                        .'En consecuencia, si la parte compradora desistiere unilateralmente de la compraventa o no compareciere al otorgamiento de la escritura pública en el plazo fijado, '
                        .'perderá íntegramente la cantidad entregada en concepto de arras en favor de la parte vendedora. Si fuere la parte vendedora quien desistiere de la venta o se negare a otorgar la escritura pública, '
                        .'vendrá obligada a devolver a la parte compradora el importe de las arras duplicado.',
                ],
                'escritura_publica' => [
                    'title' => 'Plazo y otorgamiento de escritura pública de compraventa',
                    'body' => 'Las partes se comprometen a formalizar la compraventa mediante el otorgamiento de la correspondiente escritura pública ante Notario '.($contract->effective_date ? 'a más tardar el día '.$contract->effective_date->format('d/m/Y') : 'en el plazo convenido').'. '
                        .'La designación de la Notaría corresponderá a la parte compradora. En el acto del otorgamiento de la escritura pública, la parte compradora abonará la cantidad restante del precio total convenido.',
                ],
                'estado_cargas' => [
                    'title' => 'Estado del inmueble, cargas y situación arrendaticia',
                    'body' => 'La parte vendedora declara y garantiza que el inmueble objeto del presente contrato se transmitirá completamente libre de cargas, gravámenes, hipotecas, embargos, arrendatarios u ocupantes, '
                        .'y al corriente en el pago de todo tipo de tributos (IBI), gastos de comunidad de propietarios y suministros. La parte vendedora se compromete a cancelar a su costa cualquier carga registral previa a la firma notarial.',
                ],
                'distribucion_gastos' => [
                    'title' => 'Distribución de gastos e impuestos',
                    'body' => 'Los gastos e impuestos derivados de la futura compraventa se distribuirán con arreglo a ley: serán a cargo de la parte compradora los gastos de Notaría (salvo matriz), inscripción en el Registro de la Propiedad y el Impuesto sobre Transmisiones Patrimoniales (ITP) o IVA en su caso; '
                        .'y a cargo de la parte vendedora el Impuesto sobre el Incremento de Valor de los Terrenos de Naturaleza Urbana (Plusvalía municipal / IIVTNU) y los gastos de cancelación de cargas previas.',
                ],
            ],
            default => [],
        };
    }

    private function specialClauses(Contract $contract): array
    {
        $special = blank($contract->special_clauses)
            ? 'No se pactan cláusulas adicionales distintas de las contenidas en el presente documento.'
            : $contract->special_clauses;

        return ['title' => 'Cláusulas especiales', 'body' => $special];
    }

    /**
     * @return array{title: string, body: string}
     */
    private function signatureClause(Party $seller, Party $buyer): array
    {
        return [
            'title' => 'Firma',
            'body' => 'Y en prueba de conformidad, las partes firman el presente contrato por duplicado y a un solo efecto, '
                .'en el lugar y fecha indicados, declarando que se les ha entregado copia del mismo.',
        ];
    }
}
