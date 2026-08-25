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
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table td, .table th { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        .table th { background: #f1f5f9; font-size: 10px; }
        .signatures { margin-top: 40px; display: flex; justify-content: space-between; gap: 20px; }
        .signature-box { width: 48%; }
        .signature-box .sig-img { max-height: 90px; max-width: 100%; }
        .signature-box .name { font-weight: bold; font-size: 11px; margin-top: 4px; }
        .signature-box .sub { font-size: 10px; color: #4a5568; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 46px; padding-top: 4px; font-size: 10px; }
        .evidence-page { page-break-before: always; }
        .evidence-box { border: 1px solid #0f766e; border-radius: 6px; padding: 12px; margin-top: 8px; }
        .hash { font-family: 'Courier', monospace; font-size: 9px; word-break: break-all; background: #f1f5f9; padding: 4px 6px; border-radius: 4px; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 10px; font-weight: bold; background: #0f766e; color: #fff; }
        .clause-body { margin: 6px 0; line-height: 1.45; }
        .clause-body p { margin: 4px 0; text-align: justify; line-height: 1.45; }
        .clause-body ul { margin: 3px 0 6px 18px; padding: 0; list-style-type: disc; }
        .clause-body li { margin-bottom: 2.5px; line-height: 1.35; text-align: left; font-size: 10px; color: #2d3748; }
        .clause-section-header { font-weight: bold; color: #0f766e; font-size: 10.5px; margin-top: 8px; margin-bottom: 3px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; }
        .clause-sub-header { font-weight: bold; color: #1e293b; font-size: 10px; margin-top: 5px; margin-bottom: 2px; padding-left: 2px; }
    </style>
</head>
<body>
    <div class="doc-header">
        <h1>{{ strtoupper($contract->title) }}</h1>
        <div class="meta">
            <span>Referencia: <strong>{{ $contract->reference }}</strong></span>
            <span>Fecha de firma: <strong>{{ $contract->signing_date->format('d/m/Y') }}</strong></span>
            <span>Lugar: <strong>{{ $contract->city }}</strong></span>
            <span>Versión: <strong>v{{ $version->version ?? '—' }}</strong></span>
        </div>
        <div class="meta">
            <span>Régimen: <span class="badge">{{ strtoupper($contract->transaction_type) }}</span></span>
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
        <tr><td><strong>Cantidad</strong></td><td>{{ $contract->quantity }}</td><td><strong>Precio</strong></td><td>{{ number_format((float) $contract->price_amount, 2, ',', '.') }} {{ $contract->currency }}</td></tr>
        <tr><td><strong>Impuestos</strong></td><td>{{ number_format((float) $contract->tax_amount, 2, ',', '.') }} {{ $contract->currency }}</td><td><strong>Total</strong></td><td>{{ number_format((float) $contract->total_amount, 2, ',', '.') }} {{ $contract->currency }}</td></tr>
    </table>

    <h2>Exposición</h2>
    <p>{{ $contract->object_description }}</p>

    @foreach(($version->clauses ?? $contract->clauses ?? []) as $clause)
        <h2>{{ $clause['title'] }}</h2>
        <div class="clause-body">{!! \App\Services\ClauseFormatter::formatHtml($clause['body'], true) !!}</div>
    @endforeach

    @if($contract->legal_notes)
        <h2>Notas fiscales</h2>
        <p>{{ $contract->legal_notes }}</p>
    @endif

    <h2>Firmas</h2>
    <div class="signatures">
        @foreach([$contract->seller(), $contract->buyer()] as $party)
            @if($party)
                @php
                    $sig = collect($signatures)->firstWhere('party_role', $party->role);
                @endphp
                <div class="signature-box">
                    @if($sig && $sig->signature_image_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($sig->signature_image_path))
                        <img class="sig-img" src="data:image/png;base64,{{ base64_encode(\Illuminate\Support\Facades\Storage::disk('local')->get($sig->signature_image_path)) }}">
                    @else
                        <div style="height:40px">&nbsp;</div>
                    @endif
                    <div class="name">{{ $party->displayName() }}</div>
                    <div class="sub">{{ ucfirst($party->role) }} · NIF: {{ strtoupper($party->tax_id) }}</div>
                    @if($sig)
                        <div class="sub">Firmado: {{ $sig->signed_at->format('d/m/Y H:i') }} (UTC) · {{ $sig->ip ?? 'IP no registrada' }}</div>
                    @else
                        <div class="sub">Pendiente de firma</div>
                    @endif
                    <div class="line">Firma</div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="evidence-page">
        <h1 style="font-size:15px;color:#0f766e;border-bottom:2px solid #0f766e;padding-bottom:6px;">Hoja de evidencias — Certificado de integridad</h1>

        <div class="evidence-box">
            <p><strong>Documento:</strong> {{ $contract->reference }} — {{ $contract->title }}</p>
            <p><strong>Versión firmada:</strong> v{{ $version->version ?? '—' }}</p>
            <p><strong>Sellado:</strong> {{ $sealedAt ? $sealedAt->format('d/m/Y H:i:s') . ' UTC' : '—' }}</p>
            <p><strong>Hash del contenido acordado (SHA-256):</strong></p>
            <div class="hash">{{ $version->hash ?? '—' }}</div>
            <p><strong>Hash de esta hoja y del PDF (SHA-256):</strong></p>
            <div class="hash">{{ $pdfHash }}</div>
        </div>

        <h2>Firmantes</h2>
        <table class="table">
            <tr><th>Rol</th><th>Firmante</th><th>Email</th><th>Fecha (UTC)</th><th>IP</th><th>Tipo de firma</th></tr>
            @forelse($signatures as $sig)
                <tr>
                    <td>{{ ucfirst($sig->party_role) }}</td>
                    <td>{{ $sig->signer_name }}</td>
                    <td>{{ $sig->signer_email }}</td>
                    <td>{{ $sig->signed_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $sig->ip ?? '—' }}</td>
                    <td>{{ $sig->signature_type }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin firmas registradas.</td></tr>
            @endforelse
        </table>

        <h2>Sello de tiempo (RFC 3161)</h2>
        @if($tsa)
            <div class="evidence-box">
                <p><strong>Autoridad:</strong> {{ $tsa['url'] }}</p>
                <p><strong>Hora del token:</strong> {{ $tsa['token_time'] ?? '—' }}</p>
                <p><strong>Token (TSR, base64):</strong></p>
                <div class="hash">{{ $tsa['tsr_base64'] }}</div>
            </div>
        @else
            <div class="evidence-box">
                <p><strong>Sin sello de autoridad externa.</strong> El documento lleva sello de tiempo del servidor
                ({{ $sealedAt ? $sealedAt->format('d/m/Y H:i:s') . ' UTC' : '—' }}). La integridad se verifica mediante
                el hash SHA-256. Si se desea un sello TSA externo, puede regenerarse con la autoridad configurada.</p>
            </div>
        @endif

        <h2>Marco legal</h2>
        <p style="font-size:10px;color:#4a5568;">
            Firma Electrónica Simple conforme al art. 25 del Reglamento (UE) 910/2014 (eIDAS), al no denegarse efectos
            jurídicos a una firma por el hecho de ser electrónica. La traza de auditoría (IP, fecha, agente de usuario,
            hash SHA-256) y el sello de tiempo refuerzan la integridad y la autoría conforme a los requisitos
            internacionales de la ESIGN Act y la UETA. Documento generado electrónicamente.
        </p>
    </div>
</body>
</html>
