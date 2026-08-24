<div x-data="{
    open: false,
    step: 1,
    totalSteps: 5,
    init() {
        const urlParams = new URLSearchParams(window.location.search);
        const forceOpen = urlParams.get('welcome') || urlParams.get('tour');
        const seen = localStorage.getItem('tratix_tour_completed');

        if (forceOpen || !seen) {
            setTimeout(() => {
                this.open = true;
            }, 600);
        }

        window.addEventListener('open-tour', () => {
            this.step = 1;
            this.open = true;
        });
    },
    nextStep() {
        if (this.step < this.totalSteps) {
            this.step++;
        } else {
            this.closeTour();
        }
    },
    prevStep() {
        if (this.step > 1) {
            this.step--;
        }
    },
    closeTour() {
        this.open = false;
        localStorage.setItem('tratix_tour_completed', 'true');
    }
}" 
x-cloak
x-show="open" 
class="fixed inset-0 z-50 overflow-y-auto" 
role="dialog" 
aria-modal="true">

    {{-- Backdrop with Blur --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" 
         @click="closeTour()"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-slate-900 border-2 border-emerald-500/50 rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden text-slate-100">

            {{-- Background decorative glows --}}
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

            {{-- Top Header & Progress --}}
            <div class="p-6 pb-4 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-950 border border-emerald-800 flex items-center justify-center text-emerald-400 font-black text-xs">
                        T
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guía de Inicio Rápido Tratix</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-emerald-400" x-text="`Paso ${step} de ${totalSteps}`"></span>
                    <button type="button" @click="closeTour()" class="text-slate-400 hover:text-white text-sm p-1 transition" title="Cerrar guía">
                        ✕
                    </button>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-slate-950 h-1.5">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full transition-all duration-300" :style="`width: ${(step / totalSteps) * 100}%`"></div>
            </div>

            {{-- Modal Body by Steps --}}
            <div class="p-6 sm:p-8 min-h-[340px] flex flex-col justify-between">
                
                {{-- STEP 1: WELCOME --}}
                <div x-show="step === 1" x-transition.opacity class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs font-bold">
                        <span>🚀 Bienvenido a la nueva era contractual</span>
                    </div>
                    <h2 class="text-2xl font-black text-white">Crea, negocia y firma contratos con plena validez legal</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Tratix es tu plataforma integral para formalizar acuerdos entre particulares y empresas con la máxima seguridad jurídica, tecnología OCR de lectura de documentos y firma electrónica europea (eIDAS).
                    </p>

                    <div class="grid grid-cols-2 gap-3 pt-2 text-xs">
                        <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                            <span class="font-bold text-emerald-400 block mb-0.5">⚡ Asistente Inteligente</span>
                            <span class="text-slate-400">Plantillas adaptadas con ejemplos en 1 clic.</span>
                        </div>
                        <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                            <span class="font-bold text-emerald-400 block mb-0.5">🪪 Escáner OCR de DNI</span>
                            <span class="text-slate-400">Lectura y adjunto automático de documentos oficiales.</span>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: PROFILE & FISCAL DATA --}}
                <div x-show="step === 2" x-transition.opacity class="space-y-4" style="display: none;">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-950/80 border border-blue-500/40 text-blue-300 text-xs font-bold">
                        <span>👤 Paso 1: Configura tu Identidad Fiscal</span>
                    </div>
                    <h2 class="text-2xl font-black text-white">Guarda tus datos una sola vez</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        En la sección <strong class="text-white">"Mi Perfil"</strong> puedes guardar tus datos identificativos y fiscales por defecto (NIF/CIF, domicilio social, teléfono y actividad).
                    </p>

                    <div class="p-4 bg-slate-950/80 rounded-2xl border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold">
                            <span>✨</span> ¿Qué ventaja tiene?
                        </div>
                        <p class="text-slate-400 leading-relaxed">
                            Cada vez que redactes un contrato como comprador o vendedor, Tratix rellenará automáticamente tus datos fiscales, evitando errores tipográficos y ahorrándote tiempo.
                        </p>
                    </div>
                </div>

                {{-- STEP 3: CONTRACT CREATION & TEMPLATES --}}
                <div x-show="step === 3" x-transition.opacity class="space-y-4" style="display: none;">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-950/80 border border-amber-500/40 text-amber-300 text-xs font-bold">
                        <span>📝 Paso 2: Redacta con Plantillas y Ejemplos</span>
                    </div>
                    <h2 class="text-2xl font-black text-white">Elige tu rol y autocompleta con 1 clic</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Al crear un contrato, indica si actúas como <strong class="text-emerald-400">Vendedor</strong> o como <strong class="text-blue-400">Comprador</strong>.
                    </p>

                    <div class="p-4 bg-slate-950/80 rounded-2xl border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-amber-400 font-bold">
                            <span>✨</span> Botones "Usar ejemplo / sugerencia":
                        </div>
                        <p class="text-slate-400 leading-relaxed">
                            Junto a cada campo verás botones interactivos para insertar ejemplos redactados por abogados expertos según el tipo de contrato (vehículo, vivienda, arras, servicios o compraventa mercantil).
                        </p>
                    </div>
                </div>

                {{-- STEP 4: OCR SCANNER & COUNTERPARTY DELEGATION --}}
                <div x-show="step === 4" x-transition.opacity class="space-y-4" style="display: none;">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs font-bold">
                        <span>🪪 Paso 3: Escáner DNI y Delegación</span>
                    </div>
                    <h2 class="text-2xl font-black text-white">¿No tienes los datos de la otra parte?</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        ¡No te preocupes! Tratix te ofrece dos opciones sencillas:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                            <span class="font-bold text-emerald-400 block">📷 Escáner Anverso + Reverso</span>
                            <span class="text-slate-400">Sube fotos del documento oficial para extraer los datos y adjuntarlos automáticamente.</span>
                        </div>
                        <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                            <span class="font-bold text-blue-400 block">🤝 Delegar a la contraparte</span>
                            <span class="text-slate-400">Envía un enlace privado a la otra parte para que rellene sus propios datos y suba su DNI.</span>
                        </div>
                    </div>
                </div>

                {{-- STEP 5: REVIEW, DOCUMENTATION & SIGNING --}}
                <div x-show="step === 5" x-transition.opacity class="space-y-4" style="display: none;">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/80 border border-purple-500/40 text-purple-300 text-xs font-bold">
                        <span>✍️ Paso 4: Expediente, Negociación y Firma</span>
                    </div>
                    <h2 class="text-2xl font-black text-white">Seguridad jurídica y firma eIDAS</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Completa el ciclo contractual con total tranquilidad y respaldo probatorio:
                    </p>

                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span><strong>Documentación Obligatoria:</strong> Adjunta la ficha técnica, permiso de circulación o nota simple según el contrato.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span><strong>Negociación Colaborativa:</strong> Ambas partes pueden proponer cambios y revisar el borrador antes de firmar.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span><strong>Firma Electrónica Simple/Avanzada:</strong> Código OTP verificado, sellado temporal y custodia criptográfica SHA-256.</span>
                        </li>
                    </ul>
                </div>

                {{-- Footer Controls --}}
                <div class="pt-6 border-t border-slate-800 flex items-center justify-between gap-3">
                    <button type="button" 
                            x-show="step > 1" 
                            @click="prevStep()" 
                            class="btn-outline text-xs px-4 py-2 font-semibold">
                        ← Anterior
                    </button>
                    <button type="button" 
                            x-show="step === 1" 
                            @click="closeTour()" 
                            class="text-xs text-slate-400 hover:text-slate-200">
                        Saltar tutorial
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="nextStep()" 
                                class="btn-primary text-xs px-6 py-2.5 font-bold shadow-lg shadow-emerald-950">
                            <span x-text="step === totalSteps ? '🎉 ¡Empezar a usar Tratix!' : 'Siguiente paso →'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
