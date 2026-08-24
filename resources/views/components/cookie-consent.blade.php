@props([
    'analyticsId' => config('services.google.analytics_id'),
    'adsenseClient' => config('services.google.adsense_client'),
])

<div x-data="cookieConsentManager({
        analyticsId: '{{ $analyticsId }}',
        adsenseClient: '{{ $adsenseClient }}'
    })"
    x-init="init()"
    x-cloak
    class="relative z-50">

    {{-- 1. FLOATING COOKIE BANNER (First Layer) --}}
    <div x-show="bannerOpen && !modalOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed bottom-3 left-3 right-3 sm:left-6 sm:bottom-6 sm:max-w-xl bg-slate-900/95 backdrop-blur-md border-2 border-emerald-500/60 rounded-3xl p-5 sm:p-6 shadow-2xl shadow-emerald-950/60 text-slate-200 text-xs space-y-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-banner-title">
        
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-950 border border-emerald-800 flex items-center justify-center text-xl shrink-0">
                🍪
            </div>
            <div class="space-y-1">
                <h3 id="cookie-banner-title" class="text-sm font-bold text-white flex items-center gap-2">
                    Control de Privacidad y Cookies
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800">
                        RGPD / AEPD
                    </span>
                </h3>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Utilizamos cookies propias y de terceros para garantizar la autenticidad en la firma electrónica, analizar el tráfico con Google Analytics y personalizar anuncios con Google AdSense. Puedes aceptar todas, rechazarlas o personalizar tus preferencias.
                </p>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2">
            <a href="{{ route('privacy') }}#cookies" class="text-[11px] text-emerald-400 hover:underline font-semibold flex items-center gap-1">
                <span>📄 Política de Cookies</span>
            </a>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" 
                    @click="openPreferences()" 
                    class="px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                    ⚙️ Configurar
                </button>
                <button type="button" 
                    @click="rejectNonEssential()" 
                    class="px-3 py-1.5 rounded-xl border border-rose-800/80 bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 text-xs font-semibold transition">
                    Rechazar
                </button>
                <button type="button" 
                    @click="acceptAll()" 
                    class="btn-primary text-xs px-4 py-1.5 font-bold shadow-md shadow-emerald-950">
                    Aceptar Todo
                </button>
            </div>
        </div>
    </div>

    {{-- 2. PREFERENCES MODAL (Second Layer) --}}
    <div x-show="modalOpen" 
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div @click.away="modalOpen = false" 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-slate-900 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6 text-xs text-slate-300">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-emerald-950 border border-emerald-800 flex items-center justify-center text-xl">⚙️</span>
                    <div>
                        <h2 class="text-base font-bold text-white">Centro de Preferencias de Cookies</h2>
                        <span class="text-[11px] text-slate-400">Personaliza qué datos compartes con Tratix y sus proveedores</span>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-white text-lg font-bold p-1">✕</button>
            </div>

            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                
                {{-- Category 1: Necessary --}}
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white text-sm">Cookies Técnicas y de Seguridad</span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-300">Siempre Activas</span>
                        </div>
                        <input type="checkbox" checked disabled class="rounded border-slate-700 bg-slate-800 text-emerald-500 opacity-60 cursor-not-allowed">
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Imprescindibles para el funcionamiento de la plataforma: gestión de sesiones autenticadas, tokens anti-CSRF, verificación OTP de firma electrónica y custodia de integridad SHA-256. No pueden ser desactivadas.
                    </p>
                </div>

                {{-- Category 2: Analytics (Google Analytics) --}}
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white text-sm">Cookies Analíticas (Google Analytics 4)</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800">Opcional</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="preferences.analytics" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Nos permiten cuantificar el número de visitantes, medir el rendimiento de la web, analizar cómo interactúan los usuarios con el generador de contratos y optimizar los tiempos de carga con IP anonimizada.
                    </p>
                </div>

                {{-- Category 3: Marketing & Ads (Google AdSense) --}}
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white text-sm">Cookies de Publicidad (Google AdSense)</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-950 text-amber-400 border border-amber-800">Opcional</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="preferences.marketing" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Utilizadas por Google AdSense para mostrar anuncios contextuales o personalizados que financian el mantenimiento y el uso gratuito de Tratix para miles de ciudadanos y autónomos.
                    </p>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <button type="button" @click="rejectNonEssential()" class="px-4 py-2 rounded-xl border border-rose-800 bg-rose-950/60 hover:bg-rose-900 text-rose-300 text-xs font-semibold transition">
                    Rechazar Todo
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="acceptAll()" class="btn-outline text-xs px-4 py-2 font-semibold">
                        Aceptar Todo
                    </button>
                    <button type="button" @click="saveCustom()" class="btn-primary text-xs px-5 py-2 font-bold shadow-lg shadow-emerald-950">
                        Guardar Preferencias
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- 3. PERSISTENT FLOATING TRIGGER (To re-open consent anytime) --}}
    <div x-show="!bannerOpen && !modalOpen" class="fixed bottom-4 left-4 z-40 hidden sm:block">
        <button type="button" 
            @click="openPreferences()" 
            class="p-2.5 rounded-full bg-slate-900/90 border border-slate-700 hover:border-emerald-500 text-slate-300 hover:text-emerald-400 shadow-xl backdrop-blur-sm transition group flex items-center gap-2 text-xs font-semibold"
            title="Configurar Cookies y Privacidad">
            <span class="text-base group-hover:rotate-45 transition-transform">🍪</span>
            <span class="hidden group-hover:inline pr-1">Cookies</span>
        </button>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cookieConsentManager', (config) => ({
        analyticsId: config.analyticsId || '',
        adsenseClient: config.adsenseClient || '',
        bannerOpen: false,
        modalOpen: false,
        preferences: {
            necessary: true,
            analytics: false,
            marketing: false
        },

        init() {
            window.addEventListener('tratix:open-cookie-preferences', () => {
                this.openPreferences();
            });

            const stored = localStorage.getItem('tratix_cookie_consent');
            if (!stored) {
                this.bannerOpen = true;
            } else {
                try {
                    const parsed = JSON.parse(stored);
                    this.preferences.analytics = !!parsed.analytics;
                    this.preferences.marketing = !!parsed.marketing;
                    this.applyConsent();
                } catch (e) {
                    this.bannerOpen = true;
                }
            }
        },

        openPreferences() {
            this.modalOpen = true;
        },

        acceptAll() {
            this.preferences.analytics = true;
            this.preferences.marketing = true;
            this.saveAndClose();
        },

        rejectNonEssential() {
            this.preferences.analytics = false;
            this.preferences.marketing = false;
            this.saveAndClose();
        },

        saveCustom() {
            this.saveAndClose();
        },

        saveAndClose() {
            const data = {
                necessary: true,
                analytics: this.preferences.analytics,
                marketing: this.preferences.marketing,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('tratix_cookie_consent', JSON.stringify(data));
            localStorage.setItem('tratix_cookies_accepted', 'true');
            this.bannerOpen = false;
            this.modalOpen = false;
            this.applyConsent();
        },

        applyConsent() {
            // 1. Google Analytics Activation
            if (this.preferences.analytics && this.analyticsId) {
                this.loadGoogleAnalytics(this.analyticsId);
            }

            // 2. Google AdSense Activation
            if (this.preferences.marketing && this.adsenseClient) {
                this.loadGoogleAdSense(this.adsenseClient);
            }

            // Dispatch global event for other components / ad slots
            window.dispatchEvent(new CustomEvent('tratix:cookie-consent-updated', {
                detail: this.preferences
            }));
        },

        loadGoogleAnalytics(id) {
            if (window.__ga_loaded) return;
            window.__ga_loaded = true;

            const script = document.createElement('script');
            script.async = true;
            script.src = `https://www.googletagmanager.com/gtag/js?id=${id}`;
            document.head.appendChild(script);

            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', id, {
                anonymize_ip: true,
                cookie_flags: 'SameSite=None;Secure'
            });
        },

        loadGoogleAdSense(client) {
            if (window.__adsense_loaded) return;
            window.__adsense_loaded = true;

            const script = document.createElement('script');
            script.async = true;
            script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${client}`;
            script.crossOrigin = 'anonymous';
            document.head.appendChild(script);
        }
    }));
});
</script>
