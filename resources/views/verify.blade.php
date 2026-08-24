<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de integridad · Tratix</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 font-sans antialiased text-slate-100">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="max-w-md w-full space-y-6">
            <a href="/" class="flex items-center gap-2 justify-center mb-6">
                <x-application-logo class="w-10 h-10" />
                <span class="text-2xl font-extrabold text-emerald-500">Tratix</span>
            </a>

            @if(!$found)
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center shadow-xl">
                    <div class="text-4xl mb-4">🔍</div>
                    <h1 class="text-xl font-bold text-white">Documento no encontrado</h1>
                    <p class="text-sm text-slate-400 mt-2">No existe un contrato sellado con esa referencia, o el documento no está disponible para verificación pública.</p>
                    <a href="{{ url('/') }}" class="inline-block mt-6 text-emerald-400 hover:underline text-sm font-medium">Volver a Tratix</a>
                </div>
            @else
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center shadow-xl space-y-4">
                    <div class="text-5xl">{{ $valid ? '✅' : '⚠️' }}</div>
                    <h1 class="text-xl font-bold text-white">{{ $valid ? 'Integridad verificada' : 'El documento no coincide' }}</h1>
                    <p class="text-sm text-slate-400">
                        @if($valid)
                            El documento <span class="font-mono text-emerald-400 font-bold">{{ $contract->reference }}</span> no ha sido alterado desde su sellado.
                        @else
                            El documento <span class="font-mono text-red-400 font-bold">{{ $contract->reference }}</span> ha sido modificado o su hash no coincide con el sellado.
                        @endif
                    </p>
                    <div class="mt-4 text-left bg-slate-900/90 border border-slate-700/80 rounded-xl p-4 text-xs space-y-2">
                        <div>
                            <span class="text-slate-500 block">Referencia</span>
                            <span class="font-mono text-sm text-slate-200">{{ $contract->reference }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Hash SHA-256 sellado</span>
                            <span class="font-mono text-[11px] text-slate-300 break-all">{{ $hash }}</span>
                        </div>
                        @if($contract->sealed_at)
                            <div>
                                <span class="text-slate-500 block">Fecha de sellado</span>
                                <span class="text-slate-200">{{ $contract->sealed_at->format('d/m/Y H:i T') }}</span>
                            </div>
                        @endif
                    </div>
                    <a href="{{ url('/') }}" class="btn-primary w-full inline-block text-xs py-3 font-bold shadow-lg shadow-emerald-950 mt-2">
                        Crear tu propio contrato sellado
                    </a>
                </div>
            @endif

            {{-- Google AdSense Sponsored Unit --}}
            <x-ad-slot />
        </div>
    </div>

    {{-- Universal Cookie Consent --}}
    <x-cookie-consent />
</body>
</html>
