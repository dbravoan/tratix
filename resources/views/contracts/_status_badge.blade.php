@php
    $config = [
        'borrador' => ['label' => 'Borrador', 'class' => 'bg-slate-800 text-slate-300'],
        'en_revision' => ['label' => 'En revisión', 'class' => 'bg-blue-100 text-blue-800'],
        'lista_para_firma' => ['label' => 'Listo para firmar', 'class' => 'bg-violet-100 text-violet-800'],
        'en_firma' => ['label' => 'En firma', 'class' => 'bg-amber-100 text-amber-800'],
        'firmado' => ['label' => 'Firmado y sellado', 'class' => 'bg-emerald-100 text-emerald-800'],
        'cancelado' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-700'],
    ];
    $cfg = $config[$status] ?? $config['borrador'];
@endphp
<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
