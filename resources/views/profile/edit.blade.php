@extends('layouts.app')

@section('title', 'Mi Perfil y Centro Premium')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{
    tab: (function() {
        const raw = new URLSearchParams(window.location.search).get('tab');
        if (raw === 'party') return 'identity';
        if (raw) return raw;
        if (new URLSearchParams(window.location.search).get('welcome')) return 'identity';
        return 'plan';
    })(),
    setTab(t) {
        if (t === 'party') t = 'identity';
        this.tab = t;
        const url = new URL(window.location);
        url.searchParams.set('tab', t);
        window.history.replaceState({}, '', url);
    }
}">

    {{-- Welcome Onboarding Banner on First Registration --}}
    @if(request('welcome') || session('status'))
        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 border-2 border-emerald-500/80 shadow-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="text-3xl sm:text-4xl shrink-0">🎉</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-white">¡Bienvenido a Tratix, {{ $user->name }}!</h2>
                    <p class="text-xs sm:text-sm text-emerald-200 mt-1">
                        Configura tus datos fiscales por defecto en la pestaña <strong>"Datos Fiscales"</strong> (NIF/CIF, domicilio y teléfono) para que la redacción de contratos sea 100% automática.
                    </p>
                </div>
            </div>
            <button type="button" @click="setTab('identity')" class="btn-primary shrink-0 text-xs px-5 py-2.5 font-bold shadow-lg shadow-emerald-500/30">
                🪪 Completar Datos Fiscales →
            </button>
        </div>
    @endif

    {{-- Premium Hero Profile Header --}}
    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900 via-slate-900/95 to-slate-950 p-6 sm:p-8 shadow-2xl">
        {{-- Background Glow --}}
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                {{-- Avatar with Tier Aura --}}
                <div class="relative">
                    <div class="h-20 w-20 rounded-2xl flex items-center justify-center text-2xl font-black shadow-xl
                        {{ $user->isBusiness() ? 'bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-600 text-slate-950 ring-4 ring-amber-400/30' :
                           ($user->isPro() ? 'bg-gradient-to-tr from-emerald-600 via-teal-400 to-emerald-500 text-slate-950 ring-4 ring-emerald-400/30' :
                           'bg-gradient-to-tr from-slate-700 via-slate-600 to-slate-800 text-slate-100 ring-4 ring-slate-700/50') }}">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold shadow-md
                        {{ $user->isBusiness() ? 'bg-amber-400 text-slate-950' : ($user->isPro() ? 'bg-emerald-400 text-slate-950' : 'bg-slate-700 text-slate-300') }}">
                        {{ $user->isBusiness() ? '👑' : ($user->isPro() ? '✨' : '⚡') }}
                    </span>
                </div>

                {{-- User Information --}}
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $user->name }}</h1>
                        @if($user->isBusiness())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-amber-400/20 to-yellow-400/20 text-amber-300 border border-amber-400/40 shadow-sm">
                                <span>👑</span> BUSINESS TIER
                            </span>
                        @elseif($user->isPro())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-emerald-400/20 to-teal-400/20 text-emerald-300 border border-emerald-400/40 shadow-sm">
                                <span>✨</span> PRO MEMBER
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                <span>⚡</span> PLAN GRATUITO
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-400 mt-1 flex items-center gap-2">
                        <span>{{ $user->email }}</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-800/60">
                            ✓ Verificado
                        </span>
                        <span class="text-slate-600">·</span>
                        <span class="text-xs text-slate-400">Miembro desde {{ $user->created_at->translatedFormat('F Y') }}</span>
                    </p>
                </div>
            </div>

            {{-- Quick Action CTA --}}
            <div class="flex flex-wrap items-center gap-3">
                @if(!$user->isPro())
                    <a href="{{ route('billing.pricing') }}" class="btn-primary flex items-center gap-2 px-5 py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transition-transform">
                        <span>🚀</span> Mejorar a Pro (Ilimitado)
                    </a>
                @elseif(!$user->isBusiness())
                    <a href="{{ route('billing.pricing') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-950 hover:from-amber-400 hover:to-yellow-400 shadow-md transition">
                        <span>👑</span> Subir a Business
                    </a>
                @endif
                <a href="{{ route('contracts.create') }}" class="btn-outline flex items-center gap-2 px-4 py-2 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-300 transition">
                    <span>+</span> Redactar contrato
                </a>
            </div>
        </div>

        {{-- Metrics & Stats Ribbon --}}
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 pt-6 border-t border-slate-800/80">
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Contratos totales</span>
                <p class="text-xl sm:text-2xl font-bold text-white mt-0.5">{{ $totalContracts }}</p>
                <span class="text-[10px] text-slate-400">{{ $thisMonthContracts }} este mes</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Sellados y cerrados</span>
                <p class="text-xl sm:text-2xl font-bold text-emerald-400 mt-0.5">{{ $sealedContracts }}</p>
                <span class="text-[10px] text-emerald-300">Con hoja de evidencias</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Créditos disponibles</span>
                <p class="text-xl sm:text-2xl font-bold text-amber-400 mt-0.5">{{ $user->credits }}</p>
                <span class="text-[10px] text-slate-400">Para sellos extra</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Amigos referidos</span>
                <p class="text-xl sm:text-2xl font-bold text-teal-300 mt-0.5">{{ $userReferrals->count() }}</p>
                <span class="text-[10px] text-teal-400">Meses Pro ganados</span>
            </div>
        </div>
    </div>

    {{-- Interactive Tab Navigation --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-800 text-sm font-semibold">
        <button type="button" @click="setTab('plan')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition border shrink-0"
            :class="tab === 'plan' ? 'bg-emerald-950/60 border-emerald-500/80 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'">
            <span>💎</span>
            <span>Mi Plan & Ventajas Premium</span>
        </button>

        <button type="button" @click="setTab('identity')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition border shrink-0"
            :class="tab === 'identity' ? 'bg-emerald-950/60 border-emerald-500/80 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'">
            <span>👤</span>
            <span>Datos Fiscales & Contrato</span>
        </button>

        <button type="button" @click="setTab('rewards')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition border shrink-0"
            :class="tab === 'rewards' ? 'bg-emerald-950/60 border-emerald-500/80 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'">
            <span>🎁</span>
            <span>Referidos & Recompensas</span>
            @if($userReferrals->count() > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-500 text-slate-950">{{ $userReferrals->count() }}</span>
            @endif
        </button>

        <button type="button" @click="setTab('preferences')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition border shrink-0"
            :class="tab === 'preferences' ? 'bg-emerald-950/60 border-emerald-500/80 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'">
            <span>🔔</span>
            <span>Notificaciones</span>
        </button>

        <button type="button" @click="setTab('security')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition border shrink-0"
            :class="tab === 'security' ? 'bg-emerald-950/60 border-emerald-500/80 text-emerald-300 shadow-sm' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'">
            <span>🔐</span>
            <span>Seguridad & Cuenta</span>
        </button>
    </div>

    {{-- TAB 1: MI PLAN & VENTAJAS PREMIUM --}}
    <div x-show="tab === 'plan'" x-transition.opacity.duration.250ms class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Current Plan Status Card --}}
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado de tu Suscripción</span>
                        <h2 class="text-2xl font-bold text-white mt-1">
                            Plan {{ $user->planName() }}
                            @if($user->isPro())
                                <span class="text-xs font-semibold text-emerald-400 ml-2">● Activo & Ilimitado</span>
                            @else
                                <span class="text-xs font-semibold text-slate-400 ml-2">● {{ $thisMonthContracts }}/2 contratos usados</span>
                            @endif
                        </h2>
                    </div>
                    <a href="{{ route('billing.pricing') }}" class="btn-outline text-xs px-3.5 py-2 font-bold hover:border-emerald-500">
                        Ver comparativa de planes ↗
                    </a>
                </div>

                {{-- Progress bar for Free tier --}}
                @if($user->isFree())
                    <div class="space-y-2 p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <div class="flex justify-between text-xs font-semibold text-slate-300">
                            <span>Límite mensual gratuito</span>
                            <span class="text-emerald-400">{{ $thisMonthContracts }} de 2 contratos redactados</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ min(100, ($thisMonthContracts / 2) * 100) }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">El contador se reinicia automáticamente el primer día del próximo mes.</p>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-emerald-950/30 border border-emerald-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✨</span>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-300">Contratos Ilimitados Habilitados</h4>
                                <p class="text-xs text-slate-300">Puedes redactar, negociar y firmar sin ninguna limitación mensual.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Sin límite</span>
                    </div>
                @endif

                {{-- Unlocked Premium Features Grid --}}
                <div>
                    <h3 class="text-sm font-bold text-slate-200 mb-3">Tus Beneficios y Capacidades Incluidas</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">📜</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Todas las 10 Plantillas Legales</h4>
                                <p class="text-slate-400 mt-0.5">Arras, vehículos, inmuebles, servicios, alquiler, préstamos, NDAs, bienes muebles y cesiones.</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">🛡️</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Sellado eIDAS y Hoja de Evidencias</h4>
                                <p class="text-slate-400 mt-0.5">Hash criptográfico SHA-256, trazabilidad de IPs, sellado de tiempo TSA y custodia legal.</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">⏳</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Enlaces de Firma Extensibles</h4>
                                <p class="text-slate-400 mt-0.5">{{ $user->isPro() ? 'Vigencia extendida de 30 días para firmas sin caducidad apresurada.' : 'Vigencia de 7 días para enlaces de firma.' }}</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">💬</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Negociación y Comentarios en Vivo</h4>
                                <p class="text-slate-400 mt-0.5">Revisión colaborativa con la contraparte, propuestas de modificación y control de versiones.</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">🪪</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Escáner OCR de DNI/NIE con Cámara</h4>
                                <p class="text-slate-400 mt-0.5">Lectura automática de documentos de identidad, anverso y reverso con validación de código.</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/50 flex items-start gap-3">
                            <span class="text-lg">🏷️</span>
                            <div>
                                <h4 class="font-bold text-slate-100">Marca Blanca en PDFs</h4>
                                <p class="text-slate-400 mt-0.5">{{ $user->isBusiness() ? '✓ Activa: Tus contratos se generan limpios sin marcas de Tratix.' : 'Disponible en plan Business.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Demo Mode Quick Switcher (For local / test environments) --}}
                @if($gateway === 'demo')
                    <div class="p-4 rounded-2xl bg-blue-950/30 border border-blue-800/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-200">🛠️ Simulador de Planes (Modo Demostración)</span>
                            <span class="text-[10px] text-blue-300">Cambio instantáneo para pruebas</span>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <form method="POST" action="{{ route('profile.demo-plan') }}">
                                @csrf
                                <input type="hidden" name="plan" value="free">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $user->plan === 'free' ? 'bg-slate-700 text-white' : 'bg-slate-900 border border-slate-700 text-slate-300 hover:text-white' }}">
                                    ⚡ Plan Gratis
                                </button>
                            </form>
                            <form method="POST" action="{{ route('profile.demo-plan') }}">
                                @csrf
                                <input type="hidden" name="plan" value="pro">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $user->plan === 'pro' ? 'bg-emerald-500 text-slate-950' : 'bg-slate-900 border border-slate-700 text-emerald-400 hover:bg-emerald-950' }}">
                                    ✨ Plan Pro (9 €/mes)
                                </button>
                            </form>
                            <form method="POST" action="{{ route('profile.demo-plan') }}">
                                @csrf
                                <input type="hidden" name="plan" value="business">
                                <button class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $user->plan === 'business' ? 'bg-amber-400 text-slate-950' : 'bg-slate-900 border border-slate-700 text-amber-300 hover:bg-amber-950' }}">
                                    👑 Plan Business (19 €/mes)
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Extra Credits & Upgrade Sidebar Card --}}
            <div class="space-y-6">
                {{-- Credits Card --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="font-bold text-slate-100 text-sm flex items-center gap-2">
                            <span>🪙</span> Saldo de Créditos Extra
                        </h3>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800">
                            {{ $user->credits }} disponibles
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Los créditos te permiten realizar sellados cualificados adicionales o descargar copias sin marca aunque estés en el plan gratuito.
                    </p>
                    <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-300">Precio por crédito suelto:</span>
                        <span class="font-bold text-amber-400">2,00 €</span>
                    </div>
                    <a href="{{ route('referrals.index') }}" class="btn-outline w-full text-center block text-xs py-2 hover:border-amber-500 hover:text-amber-300">
                        🎁 Conseguir créditos gratis invitando amigos
                    </a>
                </div>

                {{-- Pro Promo Card (if free) --}}
                @if(!$user->isPro())
                    <div class="bg-gradient-to-br from-emerald-950/60 to-slate-900 border border-emerald-500/40 rounded-3xl p-6 shadow-xl space-y-4">
                        <span class="text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Recomendado
                        </span>
                        <h3 class="text-lg font-bold text-white">Hazte Pro por 9 €/mes</h3>
                        <ul class="text-xs space-y-2 text-slate-300">
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">✓</span> Redacta contratos sin límite mensual
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">✓</span> Custodia extendida y sellado notarial
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">✓</span> Soporte prioritario
                            </li>
                        </ul>
                        <a href="{{ route('billing.pricing') }}" class="btn-primary w-full text-center block py-2.5 text-xs font-bold shadow-lg">
                            Activar Suscripción Pro
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 2: DATOS FISCALES & CONFIGURACIÓN POR DEFECTO --}}
    <div x-show="tab === 'identity'" x-transition.opacity.duration.250ms class="space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            @csrf
            @method('patch')

            <div class="border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-400 uppercase tracking-wider">
                    <span>💡 Autocompletado inteligente de contratos</span>
                </div>
                <h2 class="text-xl font-bold text-white mt-1">Identidad Legal y Datos Fiscales por Defecto</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Guarda tus datos fiscales una sola vez. Cuando redactes un nuevo contrato como parte vendedora o compradora, Tratix rellenará automáticamente tu información.
                </p>
            </div>

            {{-- Basic Identity --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Nombre y Apellidos completos *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Correo Electrónico principal *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Tipo de Titular</label>
                    <select name="party_type" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="particular" @selected(old('party_type', $user->party_type) === 'particular')>Persona Física / Particular</option>
                        <option value="empresa" @selected(old('party_type', $user->party_type) === 'empresa')>Empresa / Sociedad / Autónomo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">NIF / CIF / NIE por defecto</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', $user->tax_id) }}" placeholder="p. ej. 12345678Z / B12345678" class="w-full uppercase border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Razón Social / Nombre Comercial (si procede)</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="p. ej. Mi Empresa S.L." class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Teléfono móvil / WhatsApp de contacto</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+34 600 000 000" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Domicilio Fiscal / Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Calle, número, piso o polígono industrial" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Código Postal</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" placeholder="28001" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Ciudad y País</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="Madrid" class="col-span-2 border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="text" name="country" value="{{ old('country', $user->country ?? 'ES') }}" maxlength="2" placeholder="ES" class="uppercase text-center border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-2 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <p class="text-xs text-slate-400">Los datos se transmiten cifrados y conforme al RGPD / LOPDGDD.</p>
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-bold shadow-lg shadow-emerald-500/20">
                    Guardar Datos Fiscales
                </button>
            </div>
        </form>
    </div>

    {{-- TAB 3: REFERIDOS & RECOMPENSAS --}}
    <div x-show="tab === 'rewards'" x-transition.opacity.duration.250ms class="space-y-6" x-data="{
        copied: false,
        link: @js($referralLink),
        copy() {
            navigator.clipboard.writeText(this.link).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            });
        }
    }">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">🎁 Programa de Invitaciones</span>
                <h2 class="text-xl font-bold text-white mt-1">Invita a otros profesionales y gana meses Pro gratis</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Comparte tu enlace exclusivo. Por cada usuario que cree su cuenta y pruebe Tratix, <strong>ambos recibiréis 1 mes de Pro gratis</strong> y 1 crédito suelto para sellados extra.
                </p>
            </div>

            {{-- Link Box --}}
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                <label class="block text-xs font-semibold text-slate-300">Tu enlace único de recomendación</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 bg-slate-900 border border-slate-700 px-3.5 py-2.5 rounded-xl text-xs font-mono text-emerald-300 break-all select-all flex items-center">
                        {{ $referralLink }}
                    </div>
                    <button type="button" @click="copy()" class="btn-primary text-xs px-5 py-2.5 font-bold shrink-0">
                        <span x-text="copied ? '✓ ¡Copiado!' : 'Copiar enlace'"></span>
                    </button>
                </div>

                {{-- Social Share Shortcuts --}}
                <div class="pt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-400">Compartir en:</span>
                    <a href="https://wa.me/?text={{ rawurlencode('Hola, redacta y firma contratos con plena validez legal en Tratix. Regístrate con mi enlace y te regalan 1 mes Pro gratis: ' . $referralLink) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-950/60 text-emerald-300 border border-emerald-800 hover:bg-emerald-900/60 transition font-medium">
                        <span>💬</span> WhatsApp
                    </a>
                    <a href="mailto:?subject={{ rawurlencode('1 mes gratis de Tratix para redactar contratos legales') }}&body={{ rawurlencode('Hola, te comparto Tratix para generar contratos con sellado legal eIDAS. Si te registras desde este enlace consigues 1 mes gratis de Pro: ' . $referralLink) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-950/60 text-blue-300 border border-blue-800 hover:bg-blue-900/60 transition font-medium">
                        <span>✉️</span> Email
                    </a>
                </div>
            </div>

            {{-- Referrals History Table --}}
            <div>
                <h3 class="text-sm font-bold text-slate-200 mb-3">Historial de Invitados ({{ $userReferrals->count() }})</h3>
                <div class="overflow-x-auto rounded-2xl border border-slate-800">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3.5">Usuario invitado</th>
                                <th class="p-3.5">Fecha de registro</th>
                                <th class="p-3.5">Recompensa</th>
                                <th class="p-3.5 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($userReferrals as $ref)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-medium text-slate-100">
                                        {{ $ref->referred?->name ?? 'Usuario Tratix' }}
                                    </td>
                                    <td class="p-3.5 text-slate-400">
                                        {{ $ref->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3.5 text-emerald-400 font-semibold">
                                        +1 Mes Pro & +1 Crédito
                                    </td>
                                    <td class="p-3.5 text-right">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-700">
                                            ✓ Aplicada
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500 italic">
                                        Aún no has invitado a ningún contacto. ¡Comparte tu enlace y empieza a sumar meses gratis!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 4: NOTIFICACIONES & PREFERENCIAS --}}
    <div x-show="tab === 'preferences'" x-transition.opacity.duration.250ms class="space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            @csrf
            @method('patch')

            {{-- Keep existing name/email to pass validation --}}
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-xl font-bold text-white">Preferencias de Notificación y Alertas</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Configura qué correos electrónicos deseas recibir en tiempo real sobre tus contratos.
                </p>
            </div>

            <div class="space-y-4">
                <label class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <span class="text-sm font-bold text-slate-100 flex items-center gap-2">
                            <span>💬</span> Comentarios y observaciones de la contraparte
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">Recibe un correo cuando alguien comente sobre una cláusula de tus contratos.</p>
                    </div>
                    <input type="checkbox" name="notify_comments" value="1" @checked(old('notify_comments', $user->notify_comments ?? true))
                           class="rounded h-5 w-5 border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <span class="text-sm font-bold text-slate-100 flex items-center gap-2">
                            <span>✏️</span> Propuestas de modificación de cláusulas
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">Aviso inmediato cuando la otra parte sugiera un cambio formal en la redacción.</p>
                    </div>
                    <input type="checkbox" name="notify_proposals" value="1" @checked(old('notify_proposals', $user->notify_proposals ?? true))
                           class="rounded h-5 w-5 border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <span class="text-sm font-bold text-slate-100 flex items-center gap-2">
                            <span>✍️</span> Firmas electrónicas y contratos sellados
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">Aviso cuando un firmante complete su rúbrica y envío del PDF definitivo sellado.</p>
                    </div>
                    <input type="checkbox" name="notify_signatures" value="1" @checked(old('notify_signatures', $user->notify_signatures ?? true))
                           class="rounded h-5 w-5 border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <span class="text-sm font-bold text-slate-100 flex items-center gap-2">
                            <span>📊</span> Resumen mensual de actividad
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">Informe mensual con estadísticas de contratos cerrados y renovaciones.</p>
                    </div>
                    <input type="checkbox" name="notify_summary" value="1" @checked(old('notify_summary', $user->notify_summary ?? true))
                           class="rounded h-5 w-5 border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                </label>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-bold shadow-lg shadow-emerald-500/20">
                    Guardar Preferencias
                </button>
            </div>
        </form>
    </div>

    {{-- TAB 5: SEGURIDAD & CUENTA --}}
    <div x-show="tab === 'security'" x-transition.opacity.duration.250ms class="space-y-6">
        {{-- Change Password Form --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <h2 class="text-xl font-bold text-white">Actualizar Contraseña</h2>
                <p class="text-xs text-slate-400 mt-1">Asegúrate de que tu cuenta utiliza una contraseña larga y aleatoria para mantener la máxima seguridad.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4 max-w-xl">
                @csrf
                @method('put')

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Contraseña Actual *</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Nueva Contraseña *</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-200 mb-1">Confirmar Nueva Contraseña *</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-bold">
                        Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>

        {{-- GDPR / Privacy & Data Rights Center (RGPD / LOPDGDD) --}}
        <div id="gdpr-section" class="bg-slate-900 border border-emerald-500/30 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 text-[11px] font-bold mb-2">
                        <span>🛡️ RGPD (UE) 2016/679 · LOPDGDD 3/2018</span>
                    </div>
                    <h2 class="text-xl font-bold text-white">Privacidad, Protección de Datos y Tus Derechos</h2>
                    <p class="text-xs text-slate-400 mt-1">Controla tus datos personales, descarga tu expediente digital completo o ejerce tus derechos legales.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile.gdpr.export') }}" class="btn-primary text-xs px-4 py-2 flex items-center gap-1.5 font-bold shadow-lg shadow-emerald-950">
                        <span>📥 Descargar mis datos (Portabilidad Art. 20)</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 bg-slate-950/60 rounded-2xl border border-slate-800 space-y-2">
                    <h4 class="font-bold text-emerald-400 flex items-center gap-1.5">
                        <span>📦</span> Portabilidad e Interoperabilidad (Art. 20 RGPD)
                    </h4>
                    <p class="text-slate-400 leading-relaxed">
                        Puedes descargar en cualquier momento un archivo estructurado (<code class="text-emerald-300">JSON</code>) con todos los datos de tu cuenta, perfil fiscal, preferencias, contratos asociados y registros de firma electrónica.
                    </p>
                </div>

                <div class="p-4 bg-slate-950/60 rounded-2xl border border-slate-800 space-y-2">
                    <h4 class="font-bold text-emerald-400 flex items-center gap-1.5">
                        <span>🔒</span> Conservación y Bloqueo Legal (Art. 32 LOPDGDD)
                    </h4>
                    <p class="text-slate-400 leading-relaxed">
                        Tus documentos de identidad y contratos se custodian en almacenamiento privado cifrado. Tras la extinción del contrato, los datos se bloquean durante los plazos de prescripción legal para responder ante autoridades judiciales o tributarias.
                    </p>
                </div>
            </div>

            {{-- Rights Exercise Form --}}
            <div class="p-5 bg-slate-950/80 rounded-2xl border border-slate-800 space-y-4" x-data="{ openForm: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-100 text-sm">Formulario de Ejercicio de Derechos ARCO-POL</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Envía una solicitud formal con acuse de recibo inmediato (plazo máximo de resolución: 30 días).</p>
                    </div>
                    <button type="button" @click="openForm = !openForm" class="btn-outline text-xs px-3 py-1.5">
                        <span x-text="openForm ? 'Cerrar formulario' : 'Ejercer un derecho →'"></span>
                    </button>
                </div>

                <form x-show="openForm" x-transition.opacity method="POST" action="{{ route('profile.gdpr.request') }}" class="space-y-4 pt-3 border-t border-slate-800">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-200 mb-1">Derecho que deseas ejercer *</label>
                            <select name="right_type" required class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                                <option value="acceso">Derecho de Acceso (Art. 15 RGPD)</option>
                                <option value="rectificacion">Derecho de Rectificación (Art. 16 RGPD)</option>
                                <option value="supresion">Derecho de Supresión / Cancelación (Art. 17 RGPD)</option>
                                <option value="limitacion">Derecho a la Limitación del Tratamiento (Art. 18 RGPD)</option>
                                <option value="portabilidad">Derecho a la Portabilidad (Art. 20 RGPD)</option>
                                <option value="oposicion">Derecho de Oposición (Art. 21 RGPD)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-200 mb-1">Correo de notificación *</label>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full border border-slate-800 bg-slate-900/50 text-slate-400 rounded-xl px-3.5 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Motivo o detalle de tu solicitud *</label>
                        <textarea name="description" rows="3" required placeholder="Explica detalladamente tu solicitud (ej. rectificación de domicilio, limitación de tratamiento de un contrato concreto, etc.)." class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-slate-400">
                            También puedes escribir a <a href="mailto:{{ 'privacidad@' . request()->getHost() }}" class="text-emerald-400 underline">{{ 'privacidad@' . request()->getHost() }}</a>.
                        </span>
                        <button type="submit" class="btn-primary text-xs px-5 py-2 font-bold">
                            Registrar Solicitud Legal
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-1 flex items-center justify-between text-xs text-slate-400 border-t border-slate-800">
                <span>Consulta el texto íntegro en cualquier momento:</span>
                <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-semibold underline">
                    Política de Privacidad y Protección de Datos Completa (Capa 2) →
                </a>
            </div>
        </div>

        {{-- Danger Zone: Account Deletion --}}
        <div class="bg-rose-950/20 border border-rose-900/60 rounded-3xl p-6 sm:p-8 shadow-xl space-y-4" x-data="{ confirming: false }">
            <div class="border-b border-rose-900/40 pb-3">
                <h3 class="text-base font-bold text-rose-300">Zona de Peligro: Eliminar Cuenta</h3>
                <p class="text-xs text-rose-200/70 mt-0.5">
                    Una vez eliminada la cuenta, todos tus recursos y datos se borrarán permanentemente. Descarga tus contratos firmados antes de proceder.
                </p>
            </div>

            <button type="button" @click="confirming = true" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-900/50 hover:bg-rose-800 text-rose-200 border border-rose-700 transition">
                Eliminar mi cuenta definitivamente
            </button>

            {{-- Deletion Modal --}}
            <div x-show="confirming" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="confirming = false"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-slate-900 border border-rose-700/80 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                        <h3 class="text-lg font-bold text-white">¿Confirmas la eliminación de tu cuenta?</h3>
                        <p class="text-xs text-slate-400">
                            Por favor, introduce tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.
                        </p>

                        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                            @csrf
                            @method('delete')

                            <div>
                                <input type="password" name="password" placeholder="Introduce tu contraseña actual" required
                                       class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="confirming = false" class="btn-outline text-xs px-4 py-2">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition">
                                    Sí, eliminar mi cuenta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
