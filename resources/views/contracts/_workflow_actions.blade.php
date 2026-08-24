@php
    $token = $contract->access_token;
    $pending = $contract->proposals()->where('status', 'pending')->count();
@endphp
<div class="space-y-3 text-sm">
    @if($contract->status === 'borrador')
        <div class="p-3.5 bg-emerald-950/40 border border-emerald-800/80 rounded-lg space-y-2">
            <p class="text-xs text-emerald-200 font-semibold">🤝 Compartir con la otra parte antes de la reunión</p>
            <p class="text-xs text-slate-300">Envía el enlace para que la otra parte rellene sus datos fiscales (DNI, domicilio, etc.) y revise el borrador.</p>
            @include('contracts._share_modal', ['contract' => $contract])
        </div>

        <form method="POST" action="{{ route('contracts.send-review', $contract) }}" class="space-y-2">
            @csrf
            <p class="text-slate-400 text-xs">O envíale una invitación oficial por correo:</p>
            <label class="block text-xs font-medium text-slate-300">Email de la otra parte</label>
            <input type="email" name="invited_email" value="{{ $contract->invited_email ?? $contract->counterparty()?->email ?? old('invited_email') }}" placeholder="correo@ejemplo.com" class="w-full border-slate-600 bg-slate-900 text-slate-100 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-xs px-3 py-2" required>
            <button class="w-full bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium text-xs">Enviar invitación por email a revisión</button>
        </form>
        <div class="bg-slate-700/40 border border-slate-700 rounded-md p-3">
            <p class="text-slate-400 text-xs">¿No necesitáis negociar cambios? Puedes congelar la versión final y pasar directamente a la firma.</p>
            <form method="POST" action="{{ route('contracts.accept-final', $contract) }}" class="mt-2">
                @csrf
                <button class="w-full bg-violet-700 hover:bg-violet-600 text-white px-4 py-2 rounded-md font-medium">Congelar versión y pasar a firma (v{{ ($contract->versions()->max('version') ?? 0) + 1 }})</button>
            </form>
        </div>
        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('¿Cancelar el contrato?');">
            @csrf
            <button class="w-full text-red-600 hover:underline text-sm">Cancelar contrato</button>
        </form>
    @endif

    @if($contract->status === 'en_revision')
        <p class="text-slate-400">La otra parte revisa el borrador. Si propone cambios, aparecerán abajo y deberás aprobarlos o rechazarlos.</p>
        <div class="space-y-2">
            <a href="{{ route('contracts.review-link', $contract) }}" target="_blank" class="inline-block w-full text-center bg-blue-950/40 text-blue-300 border border-blue-800 px-4 py-2 rounded-md font-medium hover:bg-blue-900/50">Abrir enlace de revisión</a>
            @include('contracts._share_modal', ['contract' => $contract])
        </div>
        <div class="bg-slate-700/40 border border-slate-700 rounded-md p-3">
            <p class="font-medium text-slate-200 mb-1">Cuando la otra parte acepte el borrador:</p>
            <p class="text-slate-400 text-xs mb-3">Congelar la versión final bloquea el documento: ya no se podrá editar. Los cambios posteriores requieren cancelar y crear una versión nueva.</p>
            <form method="POST" action="{{ route('contracts.accept-final', $contract) }}">
                @csrf
                <button class="w-full bg-violet-700 hover:bg-violet-600 text-white px-4 py-2 rounded-md font-medium">Aceptar versión final y congelar (v{{ ($contract->versions()->max('version') ?? 0) + 1 }})</button>
            </form>
        </div>
        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('¿Cancelar el contrato?');">
            @csrf
            <button class="w-full text-rose-400 hover:underline text-sm">Cancelar contrato</button>
        </form>
    @endif

    @if($contract->status === 'lista_para_firma')
        <p class="text-slate-400">La versión v{{ $contract->latestVersion()?->version }} está congelada. Envía la firma a la otra parte.</p>
        <form method="POST" action="{{ route('contracts.send-signature', $contract) }}" class="space-y-2">
            @csrf
            <label class="block text-sm font-medium text-slate-300">Email del firmante</label>
            <input type="email" name="signer_email" value="{{ $contract->invited_email ?? '' }}" placeholder="correo@ejemplo.com" class="w-full border-slate-600 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 bg-slate-900 text-slate-100 px-3 py-2 text-sm" required>
            <button class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 px-4 py-2 rounded-md font-semibold">Enviar a firmar</button>
        </form>
        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('¿Cancelar el contrato?');">
            @csrf
            <button class="w-full text-rose-400 hover:underline text-sm">Cancelar contrato</button>
        </form>
    @endif

    @if($contract->status === 'en_firma')
        <p class="text-slate-400">Comparte los enlaces de firma personalizados. Cada enlace identifica directamente al firmante sin pedirle qué parte es:</p>
        <div class="space-y-2">
            <div class="p-2.5 bg-slate-900 border border-slate-700 rounded-lg space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-200">Enlace Vendedor ({{ $contract->seller()?->displayName() }}):</span>
                    <a href="{{ route('sign.show', ['token' => $token, 'role' => 'vendedor']) }}" target="_blank" class="text-emerald-400 hover:underline font-bold">Abrir ↗</a>
                </div>
                <div class="bg-slate-950 p-1.5 rounded text-[11px] font-mono text-slate-400 break-all select-all">{{ route('sign.show', ['token' => $token, 'role' => 'vendedor']) }}</div>
            </div>

            <div class="p-2.5 bg-slate-900 border border-slate-700 rounded-lg space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-200">Enlace Comprador ({{ $contract->buyer()?->displayName() }}):</span>
                    <a href="{{ route('sign.show', ['token' => $token, 'role' => 'comprador']) }}" target="_blank" class="text-emerald-400 hover:underline font-bold">Abrir ↗</a>
                </div>
                <div class="bg-slate-950 p-1.5 rounded text-[11px] font-mono text-slate-400 break-all select-all">{{ route('sign.show', ['token' => $token, 'role' => 'comprador']) }}</div>
            </div>

            @include('contracts._share_modal', ['contract' => $contract])
        </div>
        <form method="POST" action="{{ route('contracts.cancel', $contract) }}" onsubmit="return confirm('¿Cancelar el contrato? Los cambios requieren una versión nueva.');">
            @csrf
            <button class="w-full text-rose-400 hover:underline text-sm">Cancelar contrato</button>
        </form>
    @endif

    @if($contract->status === 'firmado')
        <div class="bg-emerald-950/50 border border-emerald-800 text-emerald-300 rounded-md p-3">
            <p class="font-semibold">Contrato firmado por ambas partes y sellado.</p>
            <p class="text-xs mt-1 text-emerald-200">Descarga el PDF firmado y la hoja de evidencias desde el menú superior. Usa «Verificar integridad» para comprobar que no se ha modificado.</p>
        </div>
        @if($contract->final_pdf_path)
            @include('contracts._share_modal', ['contract' => $contract])
        @endif
    @endif

    @if($contract->status === 'cancelado')
        <div class="bg-rose-950/50 border border-rose-800 text-rose-300 rounded-md p-3">
            <p class="font-semibold">Este contrato está cancelado.</p>
            <p class="text-xs mt-1 text-rose-200">Crea un contrato nuevo si necesitas continuar la operación.</p>
        </div>
    @endif
</div>
