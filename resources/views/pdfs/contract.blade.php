<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $contract->reference }} – {{ $contract->title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.5; color: #1a202c; }
        .doc-header { border-bottom: 2px solid #0f766e; padding-bottom: 10px; margin-bottom: 18px; }
        .doc-header h1 { margin: 0; font-size: 17px; color: #0f766e; }
        .doc-header .meta { margin-top: 6px; font-size: 10px; color: #4a5568; }
        .doc-header .meta span { margin-right: 14px; }
        h2 { font-size: 12px; color: #0f766e; margin: 16px 0 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }
        p { margin: 4px 0; text-align: justify; }
        .money { white-space: nowrap; }
        .signatures { margin-top: 40px; display: flex; justify-content: space-between; gap: 20px; }
        .signature-box { width: 48%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 60px; padding-top: 6px; font-size: 10px; }
        .signature-box .name { font-weight: bold; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table td, .table th { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        .table th { background: #f1f5f9; font-size: 10px; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-b2b { background: #dbeafe; color: #1e40af; }
        .badge-b2c { background: #dcfce7; color: #166534; }
        .badge-c2c { background: #fef3c7; color: #92400e; }
        .badge-c2b { background: #fce7f3; color: #9d174d; }
        .issue-error { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; margin: 3px 0; }
        .issue-warning { background: #fffbeb; color: #92400e; padding: 4px 8px; border-radius: 4px; margin: 3px 0; }
    </style>
</head>
<body>
    <div class="doc-header">
        <h1>{{ strtoupper($contract->title) }}</h1>
        <div class="meta">
            <span>Referencia: <strong>{{ $contract->reference }}</strong></span>
            <span>Fecha de firma: <strong>{{ $contract->signing_date->format('d/m/Y') }}</strong></span>
            <span>Lugar: <strong>{{ $contract->city }}</strong></span>
            @if($contract->effective_date)
                <span>Efectividad: <strong>{{ $contract->effective_date->format('d/m/Y') }}</strong></span>
            @endif
        </div>
        <div class="meta">
            <span>Régimen:
                <span class="badge badge-{{ $contract->transaction_type }}">{{ strtoupper($contract->transaction_type) }}</span>
            </span>
            <span>Ámbito: <strong>{{ $contract->jurisdiction }}</strong></span>
        </div>
    </div>

    @if($contract->seller() && $contract->buyer())
        <h2>Partes intervinientes</h2>
        <table class="table">
            <tr><th>Rol</th><th>Identificación</th><th>NIF / N.º IVA</th><th>Tipo</th><th>Domicilio</th></tr>
            @foreach([$contract->seller(), $contract->buyer()] as $party)
                <tr>
                    <td>{{ ucfirst($party->role) }}</td>
                    <td>{{ $party->displayName() }}</td>
                    <td>{{ strtoupper($party->tax_id_country) !== 'ES' ? strtoupper($party->tax_id_country) . '-' : '' }}{{ strtoupper($party->tax_id) }}</td>
                    <td>{{ $party->party_type }}</td>
                    <td>{{ $party->address }}, {{ $party->postal_code }} {{ $party->city }}, {{ strtoupper($party->country) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <h2>Datos económicos</h2>
    <table class="table">
        <tr><td><strong>Tipo de contrato</strong></td><td>{{ $contract->contract_type }}</td><td><strong>Objeto</strong></td><td>{{ $contract->object_type ?? '—' }}</td></tr>
        <tr><td><strong>Cantidad</strong></td><td>{{ $contract->quantity }}</td><td><strong>Precio</strong></td><td class="money">{{ number_format((float) $contract->price_amount, 2, ',', '.') }} {{ $contract->currency }}</td></tr>
        <tr><td><strong>Impuestos</strong></td><td class="money">{{ number_format((float) $contract->tax_amount, 2, ',', '.') }} {{ $contract->currency }}</td><td><strong>Total</strong></td><td class="money">{{ number_format((float) $contract->total_amount, 2, ',', '.') }} {{ $contract->currency }}</td></tr>
    </table>

    <h2>Exposición</h2>
    <p>{{ $contract->object_description }}</p>

    @foreach(($contract->clauses ?? []) as $clause)
        <h2>{{ $clause['title'] }}</h2>
        <p>{{ $clause['body'] }}</p>
    @endforeach

    @if(($contract->legal_notes))
        <h2>Notas fiscales</h2>
        <p>{{ $contract->legal_notes }}</p>
    @endif

    <div class="signatures">
        @foreach([$contract->seller(), $contract->buyer()] as $party)
            @if($party)
                <div class="signature-box">
                    <div class="name">{{ $party->displayName() }}</div>
                    <div>NIF: {{ strtoupper($party->tax_id) }}</div>
                    <div>{{ $party->role }}<br>{{ $party->signature_city ?? $contract->city }}, {{ ($party->signature_date ?? $contract->signing_date)->format('d/m/Y') }}</div>
                    <div class="line">Firma</div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="doc-footer" style="margin-top: 24px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; text-align: center;">
        @if(!empty($white_label))
            Documento generado electrónicamente con firma electrónica y sellado.
        @else
            Generado con Tratix · Plataforma de contratos con firma electrónica (eIDAS) y sellado de evidencias.
        @endif
    </div>
</body>
</html>
