@props([
    'title' => 'Información básica sobre protección de datos (RGPD / LOPDGDD)',
    'purpose' => 'Gestión, redacción y formalización de contratos legales y generación de firma electrónica con valor probatorio.',
    'legitimation' => 'Ejecución de la relación contractual (art. 6.1.b RGPD) y cumplimiento de obligaciones legales (art. 6.1.c RGPD).',
    'recipients' => 'La contraparte contratante y proveedores tecnológicos necesarios en calidad de encargados de tratamiento. No se ceden datos a terceros salvo obligación legal.',
    'rights' => 'Acceso, rectificación, supresión, limitación, portabilidad y oposición, escribiendo a privacidad@' . request()->getHost() . ' o desde el panel de usuario.',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'bg-slate-900/90 border border-emerald-500/30 rounded-lg p-3 text-xs text-slate-300 shadow-sm']) }} x-data="{ expanded: false }">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-emerald-400 text-sm">🛡️</span>
            <span class="font-semibold text-slate-100">{{ $title }}</span>
        </div>
        <button type="button" @click="expanded = !expanded" class="text-emerald-400 hover:text-emerald-300 text-[11px] underline flex items-center gap-1 font-medium transition">
            <span x-text="expanded ? 'Ocultar detalles' : 'Ver información legal'"></span>
            <span x-text="expanded ? '▲' : '▼'" class="text-[9px]"></span>
        </button>
    </div>

    <div x-show="expanded" x-transition.opacity.duration.200ms class="mt-3 pt-3 border-t border-slate-800 space-y-2 text-[11px] leading-relaxed">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-1 sm:gap-2">
            <span class="font-semibold text-slate-400">Responsable:</span>
            <span class="sm:col-span-3 text-slate-200">{{ config('app.name') }} (Plataforma tecnológica de formalización contractual)</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-1 sm:gap-2">
            <span class="font-semibold text-slate-400">Finalidad:</span>
            <span class="sm:col-span-3 text-slate-200">{{ $purpose }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-1 sm:gap-2">
            <span class="font-semibold text-slate-400">Legitimación:</span>
            <span class="sm:col-span-3 text-slate-200">{{ $legitimation }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-1 sm:gap-2">
            <span class="font-semibold text-slate-400">Destinatarios:</span>
            <span class="sm:col-span-3 text-slate-200">{{ $recipients }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-1 sm:gap-2">
            <span class="font-semibold text-slate-400">Derechos:</span>
            <span class="sm:col-span-3 text-slate-200">{{ $rights }} Tienes derecho a presentar una reclamación ante la AEPD (www.aepd.es).</span>
        </div>
        <div class="pt-1.5 flex items-center justify-between border-t border-slate-800/80 text-[11px]">
            <span class="text-slate-400">Información adicional detallada:</span>
            <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-semibold underline">
                Consultar Política de Privacidad completa (Capa 2) →
            </a>
        </div>
    </div>
</div>
