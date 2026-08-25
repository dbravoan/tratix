<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Firma del contrato – {{ $contract->reference }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-950 font-sans antialiased text-slate-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-emerald-950/70 border border-emerald-600 text-emerald-200 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <span class="text-lg">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-950/70 border border-rose-600 text-rose-200 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-950/70 border border-rose-600 text-rose-200 px-4 py-3 rounded-xl text-sm">
                <p class="font-bold mb-1">Hay errores que revisar:</p>
                <ul class="list-disc ml-5 space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        Proceso de Firma Electrónica
                    </span>
                    <h1 class="text-2xl font-bold text-slate-100 mt-2">{{ $contract->title }}</h1>
                    <p class="text-xs text-slate-400 mt-1">
                        Referencia: <span class="font-mono text-emerald-400 font-bold">{{ $contract->reference }}</span> ·
                        {{ $contract->city }}, {{ $contract->signing_date?->format('d/m/Y') }}
                    </p>
                </div>
                @if($contract->status === 'firmado')
                    <a href="{{ route('sign.download', $token) }}" class="btn-primary flex items-center gap-2 px-5 py-2.5 text-xs font-bold shadow-lg">
                        <span>⬇️</span> Descargar PDF firmado y sellado
                    </a>
                @endif
            </div>

            {{-- Signature Progress by Identified Parties --}}
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Vendedor --}}
                <div class="p-4 rounded-xl border transition {{ $activeRole === 'vendedor' ? 'border-emerald-500 bg-emerald-950/30' : 'border-slate-800 bg-slate-900/60' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-blue-950 text-blue-300 border border-blue-800">
                            Parte Vendedora
                        </span>
                        @if($sellerSigned)
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-900/80 text-emerald-300 border border-emerald-600">
                                ✓ Firmado
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-900/80 text-amber-300 border border-amber-600">
                                ○ Pendiente
                            </span>
                        @endif
                    </div>
                    <p class="font-bold text-sm text-slate-100">{{ $seller?->displayName() }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">NIF/CIF: <span class="font-mono">{{ $seller?->tax_id }}</span></p>
                    @if(!$sellerSigned && $activeRole !== 'vendedor')
                        <a href="{{ route('sign.show', ['token' => $token, 'role' => 'vendedor']) }}" class="inline-block mt-2 text-xs text-emerald-400 hover:underline font-semibold">
                            → Firmar como Vendedor
                        </a>
                    @endif
                </div>

                {{-- Comprador --}}
                <div class="p-4 rounded-xl border transition {{ $activeRole === 'comprador' ? 'border-emerald-500 bg-emerald-950/30' : 'border-slate-800 bg-slate-900/60' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800">
                            Parte Compradora
                        </span>
                        @if($buyerSigned)
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-900/80 text-emerald-300 border border-emerald-600">
                                ✓ Firmado
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-900/80 text-amber-300 border border-amber-600">
                                ○ Pendiente
                            </span>
                        @endif
                    </div>
                    <p class="font-bold text-sm text-slate-100">{{ $buyer?->displayName() }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">NIF/CIF: <span class="font-mono">{{ $buyer?->tax_id }}</span></p>
                    @if(!$buyerSigned && $activeRole !== 'comprador')
                        <a href="{{ route('sign.show', ['token' => $token, 'role' => 'comprador']) }}" class="inline-block mt-2 text-xs text-emerald-400 hover:underline font-semibold">
                            → Firmar como Comprador
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Document Preview --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">📜 Documento Contractual a Firmar</h2>
            <p class="text-xs text-slate-300"><strong>Objeto:</strong> {{ $contract->object_description }}</p>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-2 bg-slate-950/60 p-4 rounded-xl border border-slate-800 text-xs leading-relaxed text-slate-300">
                @foreach(($contract->latestVersion()?->clauses ?? $contract->clauses ?? []) as $clause)
                    <div class="pb-2 border-b border-slate-800/80 last:border-0">
                        <h3 class="text-emerald-400 font-bold mb-1">{{ $clause['title'] }}</h3>
                        <div class="text-slate-300 leading-relaxed space-y-1.5">{!! \App\Services\ClauseFormatter::formatHtml($clause['body']) !!}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rights and Obligations --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-3">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">⚖️ Derechos y obligaciones de las partes</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 bg-slate-950/60 border {{ $activeRole === 'vendedor' ? 'border-emerald-500' : 'border-slate-800' }} rounded-xl">
                    <h3 class="font-bold text-slate-100 text-sm mb-2">Como VENDEDOR ({{ $seller?->displayName() }})</h3>
                    <p class="font-bold text-emerald-400 uppercase tracking-wide mb-1">Derechos</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300 mb-3">
                        @foreach($rights['vendedor']['rights'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <p class="font-bold text-rose-400 uppercase tracking-wide mb-1">Obligaciones</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300">
                        @foreach($rights['vendedor']['obligations'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="p-3.5 bg-slate-950/60 border {{ $activeRole === 'comprador' ? 'border-emerald-500' : 'border-slate-800' }} rounded-xl">
                    <h3 class="font-bold text-slate-100 text-sm mb-2">Como COMPRADOR ({{ $buyer?->displayName() }})</h3>
                    <p class="font-bold text-emerald-400 uppercase tracking-wide mb-1">Derechos</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300 mb-3">
                        @foreach($rights['comprador']['rights'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <p class="font-bold text-rose-400 uppercase tracking-wide mb-1">Obligaciones</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300">
                        @foreach($rights['comprador']['obligations'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Signature Form or Already Signed Banner --}}
        @if($activeSigned)
            <div class="bg-slate-900 border border-emerald-700/80 rounded-2xl p-6 shadow-xl text-center space-y-3">
                <span class="w-12 h-12 rounded-full bg-emerald-950 border border-emerald-600 flex items-center justify-center text-emerald-400 text-xl mx-auto font-bold">✓</span>
                <h3 class="text-lg font-bold text-slate-100">Firma completada para {{ $activeParty?->displayName() }}</h3>
                <p class="text-xs text-slate-300">
                    Esta parte ({{ ucfirst($activeRole) }}) ya ha firmado el documento correctamente el
                    <strong>{{ $contract->signatures->where('party_role', $activeRole)->first()?->signed_at?->format('d/m/Y H:i') }} UTC</strong>.
                </p>

                @if(!$contract->allPartiesSigned())
                    @php $otherRole = $activeRole === 'vendedor' ? 'comprador' : 'vendedor'; @endphp
                    <div class="pt-3 border-t border-slate-800">
                        <a href="{{ route('sign.show', ['token' => $token, 'role' => $otherRole]) }}" class="btn-primary inline-flex items-center gap-2 px-6 py-2.5 text-xs font-bold shadow-md">
                            <span>Firmar como {{ $otherRole === 'vendedor' ? $seller?->displayName() : $buyer?->displayName() }} ({{ ucfirst($otherRole) }}) →</span>
                        </a>
                    </div>
                @else
                    <div class="pt-3 border-t border-slate-800">
                        <a href="{{ route('sign.download', $token) }}" class="btn-primary inline-flex items-center gap-2 px-6 py-2.5 text-xs font-bold shadow-md">
                            <span>⬇️ Descargar PDF final firmado y sellado</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('sign.store', $token) }}" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                @csrf
                <input type="hidden" name="role" id="sig-role" value="{{ $activeRole }}">

                {{-- Signer Identity Banner --}}
                <div class="p-4 bg-emerald-950/40 border border-emerald-800/80 rounded-xl flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-emerald-900/60 border border-emerald-700 flex items-center justify-center text-emerald-300 text-lg">
                            ✍️
                        </span>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Identidad del Firmante Reconocida</span>
                            <h3 class="text-sm font-bold text-slate-100">
                                Firmando como {{ strtoupper($activeRole) }}: <span class="text-emerald-300">{{ $activeParty?->displayName() }}</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">Documento fiscal / NIF: <span class="font-mono text-slate-200">{{ $activeParty?->tax_id }}</span> · Domicilio: {{ $activeParty?->address }}</p>
                        </div>
                    </div>
                </div>

                {{-- Name and Email Confirmation --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Nombre completo del firmante *</label>
                        <input name="signer_name" value="{{ old('signer_name', $activeParty?->displayName()) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Email para recepción de copia firmada y evidencias *</label>
                        @if($activeParty?->email)
                            <input type="email" name="signer_email" id="signer-email" value="{{ $activeParty->email }}" readonly class="w-full border border-slate-800 bg-slate-900/90 text-emerald-300 font-mono rounded-lg px-3 py-2 text-sm cursor-not-allowed">
                            <p class="text-[11px] text-slate-400 mt-1">🔒 Email oficial registrado para la parte {{ strtoupper($activeRole) }}.</p>
                        @else
                            <input type="email" name="signer_email" id="signer-email" value="{{ old('signer_email') }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="tu-email@ejemplo.com" required>
                            <p class="text-[11px] text-slate-400 mt-1">Introduce tu correo personal para recibir la copia oficial del contrato firmado.</p>
                        @endif
                    </div>
                </div>

                {{-- FEA OTP Email Verification --}}
                @if($otpEnabled)
                    <div class="rounded-xl border border-indigo-800/80 bg-indigo-950/40 p-4 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="text-xs font-bold text-indigo-200">🔐 Verificación de Identidad por Correo (Firma FEA / eIDAS)</p>
                                <p class="text-[11px] text-indigo-300">
                                    El código de 6 dígitos se enviará al correo verificado de la parte: <strong>{{ $activeParty?->email ?? 'tu email' }}</strong>.
                                </p>
                            </div>
                            <button type="button" id="otp-send" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm shrink-0">
                                📩 Enviar código OTP
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="text" name="otp_code" id="otp-code" inputmode="numeric" maxlength="6"
                                   class="w-36 border border-indigo-700 bg-slate-950 text-slate-100 rounded-lg shadow-sm text-sm tracking-[0.5em] text-center px-3 py-2 font-mono font-bold" placeholder="••••••" required>
                            <span id="otp-status" class="text-xs text-indigo-300" aria-live="polite"></span>
                        </div>
                    </div>
                @endif

                {{-- Signature Canvas --}}
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-200">Rúbrica / Trazo de firma</label>
                    <div class="border-2 border-dashed border-slate-700 rounded-xl bg-slate-950 overflow-hidden" style="touch-action: none;">
                        <canvas id="sig-canvas" width="600" height="180" class="w-full" style="display:block; background-color: #020617;"></canvas>
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-1">
                        <div class="flex gap-2">
                            <button type="button" id="sig-clear" class="btn-outline text-xs px-3 py-1.5 hover:border-slate-500">Borrar trazo</button>
                            <button type="button" id="sig-skip" class="btn-outline text-xs px-3 py-1.5 text-slate-400 hover:text-white">Firmar con 1-clic (sin dibujo)</button>
                        </div>
                        <p id="sig-status" class="text-xs text-slate-400">Dibuja tu firma en el recuadro con el dedo o ratón.</p>
                    </div>
                    <input type="hidden" name="signature_image" id="signature-image" value="">
                    <input type="hidden" name="signature_type" id="signature-type" value="fes-canvas">
                </div>

                {{-- GDPR First Layer Notice on Signature --}}
                <x-gdpr-info-box 
                    title="Protección de datos y registro de evidencias de firma (RGPD / eIDAS)"
                    purpose="Formalización de la firma electrónica, generación de evidencias probatorias técnicas (IP, hash criptográfico SHA-256, sellado de tiempo y código OTP) y custodia contractual legal."
                    legitimation="Ejecución de la relación contractual (art. 6.1.b RGPD) y cumplimiento de obligaciones legales en materia de firma electrónica (Reglamento UE 910/2014 eIDAS y Ley 6/2020)."
                />

                {{-- Legal Consent --}}
                <div class="p-3.5 bg-slate-950/80 border border-slate-800 rounded-xl">
                    <label class="flex items-start gap-2.5 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="consent" value="1" required class="mt-0.5 rounded border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                        <span>
                            <strong>Consiento formalizar y firmar electrónicamente este contrato</strong> con plena validez legal (Reglamento UE 910/2014 eIDAS), aceptando el registro técnico de evidencias (hash criptográfico, IP, fecha y código de verificación). He leído y acepto la <a href="{{ route('privacy') }}" class="underline text-emerald-400 font-semibold" target="_blank">Política de Privacidad y Protección de Datos</a>.
                        </span>
                    </label>
                </div>

                <button type="submit" class="w-full btn-primary py-3.5 text-sm font-bold shadow-xl flex items-center justify-center gap-2 hover:shadow-emerald-500/20">
                    <span>✍️ Confirmar y Firmar Documento</span>
                </button>
            </form>
        @endif

        <p class="text-center text-[11px] text-slate-500">
            Firma electrónica simple conforme al Reglamento (UE) 910/2014 (eIDAS). Hoja de evidencias y custodia de integridad SHA-256 generada automáticamente tras la firma de ambas partes.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('sig-canvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                let drawing = false;
                let hasDrawing = false;

                const dpr = window.devicePixelRatio || 1;
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;
                ctx.scale(dpr, dpr);
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#10B981';

                function pos(e) {
                    const r = canvas.getBoundingClientRect();
                    const touch = e.touches ? e.touches[0] : e;
                    return { x: (touch.clientX - r.left) * (canvas.width / r.width) / dpr, y: (touch.clientY - r.top) * (canvas.height / r.height) / dpr };
                }

                canvas.addEventListener('mousedown', e => { drawing = true; hasDrawing = true; ctx.beginPath(); ctx.moveTo(pos(e).x, pos(e).y); });
                canvas.addEventListener('mousemove', e => { if (!drawing) return; ctx.lineTo(pos(e).x, pos(e).y); ctx.stroke(); });
                canvas.addEventListener('mouseup', () => { drawing = false; save(); });
                canvas.addEventListener('mouseleave', () => { drawing = false; save(); });

                canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; hasDrawing = true; ctx.beginPath(); ctx.moveTo(pos(e).x, pos(e).y); }, { passive: false });
                canvas.addEventListener('touchmove', e => { e.preventDefault(); if (!drawing) return; ctx.lineTo(pos(e).x, pos(e).y); ctx.stroke(); }, { passive: false });
                canvas.addEventListener('touchend', e => { e.preventDefault(); drawing = false; save(); }, { passive: false });

                function save() {
                    if (!hasDrawing) return;
                    const image = canvas.toDataURL('image/png');
                    document.getElementById('signature-image').value = image;
                    document.getElementById('signature-type').value = 'fes-canvas';
                    document.getElementById('sig-status').textContent = '✓ Trazo de firma capturado.';
                    document.getElementById('sig-status').className = 'text-xs text-emerald-400 font-semibold';
                }

                document.getElementById('sig-clear')?.addEventListener('click', () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    hasDrawing = false;
                    document.getElementById('signature-image').value = '';
                    document.getElementById('signature-type').value = 'fes-canvas';
                    document.getElementById('sig-status').textContent = 'Dibuja tu firma en el recuadro.';
                    document.getElementById('sig-status').className = 'text-xs text-slate-400';
                });

                document.getElementById('sig-skip')?.addEventListener('click', () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    hasDrawing = false;
                    document.getElementById('signature-image').value = '';
                    document.getElementById('signature-type').value = 'fes-click';
                    document.getElementById('sig-status').textContent = 'Firmarás con aceptación expresa de un clic.';
                    document.getElementById('sig-status').className = 'text-xs text-amber-400 font-semibold';
                });
            }

            const otpSend = document.getElementById('otp-send');
            if (otpSend) {
                otpSend.addEventListener('click', async () => {
                    const email = document.getElementById('signer-email')?.value.trim();
                    const role = document.getElementById('sig-role')?.value;
                    const status = document.getElementById('otp-status');
                    if (!email) {
                        status.textContent = 'Introduce primero tu email.';
                        status.className = 'text-xs text-amber-400';
                        return;
                    }
                    status.textContent = 'Enviando código...';
                    status.className = 'text-xs text-indigo-300';
                    const form = new FormData();
                    form.append('_token', document.querySelector('input[name="_token"]').value);
                    form.append('role', role);
                    form.append('signer_email', email);
                    try {
                        const res = await fetch('{{ route('sign.otp', $token) }}', {
                            method: 'POST',
                            body: form,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok || res.redirected) {
                            status.textContent = '✓ Código enviado a ' + email;
                            status.className = 'text-xs text-emerald-400 font-semibold';
                        } else {
                            status.textContent = 'Error al enviar el código. Revisa tu email.';
                            status.className = 'text-xs text-rose-400 font-semibold';
                        }
                    } catch (e) {
                        status.textContent = 'Error de conexión.';
                        status.className = 'text-xs text-rose-400';
                    }
                });
            }
        });
    </script>
</body>
</html>
