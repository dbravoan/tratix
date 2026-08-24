@props([
    'slot' => config('services.google.adsense_slot_banner', '9876543210'),
    'format' => 'auto',
    'responsive' => 'true',
    'label' => 'Publicidad Patrocinada',
    'class' => 'my-6',
])

@php
    $adsenseClient = config('services.google.adsense_client');
@endphp

@if($adsenseClient)
<div x-data="{
        marketingAllowed: false,
        adRendered: false,
        checkConsent() {
            try {
                const stored = localStorage.getItem('tratix_cookie_consent');
                if (stored) {
                    const parsed = JSON.parse(stored);
                    this.marketingAllowed = !!parsed.marketing;
                }
            } catch(e) {}
            if (this.marketingAllowed && !this.adRendered) {
                this.$nextTick(() => {
                    try {
                        (window.adsbygoogle = window.adsbygoogle || []).push({});
                        this.adRendered = true;
                    } catch(e) {}
                });
            }
        }
    }"
    x-init="checkConsent(); window.addEventListener('tratix:cookie-consent-updated', (e) => { marketingAllowed = !!e.detail.marketing; checkConsent(); })"
    class="w-full max-w-5xl mx-auto {{ $class }}">

    {{-- When Marketing Consent is GRANTED: Real AdSense Unit --}}
    <div x-show="marketingAllowed" class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-3 text-center overflow-hidden">
        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold mb-1">
            {{ $label }}
        </div>
        <div class="min-h-[90px] flex items-center justify-center">
            <ins class="adsbygoogle"
                style="display:block"
                data-ad-client="{{ $adsenseClient }}"
                data-ad-slot="{{ $slot }}"
                data-ad-format="{{ $format }}"
                data-full-width-responsive="{{ $responsive }}"></ins>
        </div>
    </div>

    {{-- When Marketing Consent is NOT granted: Friendly Subtle Placeholder --}}
    <div x-show="!marketingAllowed" class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-4 text-center space-y-1">
        <span class="text-[11px] font-bold text-slate-400 block flex items-center justify-center gap-1.5">
            <span>📢</span>
            <span>Espacio Patrocinado Google AdSense</span>
        </span>
        <p class="text-[10px] text-slate-500 max-w-md mx-auto">
            La publicidad nos ayuda a mantener las herramientas legales y el escáner de DNI gratuitos para todos. Puedes activar las cookies publicitarias en cualquier momento desde el botón de cookies.
        </p>
    </div>
</div>
@endif
