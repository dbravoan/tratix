@extends('layouts.app')

@section('title', 'Panel de Contratos y Centro de Control')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Unified Command Center Hero --}}
    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900 via-slate-900/95 to-slate-950 p-6 sm:p-8 shadow-2xl">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                {{-- Avatar --}}
                <div class="relative">
                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl flex items-center justify-center text-xl sm:text-2xl font-black shadow-xl
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

                {{-- User Info --}}
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Hola, {{ $user->name }} 👋</h1>
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
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Crea, negocia, firma electrónicamente y custodia tus contratos con plena validez legal eIDAS.
                    </p>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-tour'))" class="btn-outline flex items-center gap-2 px-3.5 py-2.5 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-300 transition text-slate-300">
                    <span>🎓</span> Guía Rápida
                </button>
                @if($user->isPro())
                    <a href="{{ route('contracts.export') }}" class="btn-outline flex items-center gap-2 px-4 py-2.5 text-xs font-semibold hover:border-slate-500 transition">
                        <span>📦</span> Exportar ZIP
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="btn-outline flex items-center gap-2 px-4 py-2.5 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-300 transition">
                    <span>⚙️</span> Mi Perfil & Plan
                </a>
                <a href="{{ route('contracts.create') }}" class="btn-primary flex items-center gap-2 px-6 py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transition-transform">
                    <span>+</span> Redactar Nuevo Contrato
                </a>
            </div>
        </div>

        {{-- Metrics & Stats Ribbon --}}
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-5 gap-3 pt-6 border-t border-slate-800/80 text-xs">
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Contratos</span>
                <p class="text-xl sm:text-2xl font-bold text-white mt-0.5">{{ $counts['all'] }}</p>
                <span class="text-[10px] text-slate-400">{{ $usedThisMonth }} en este mes</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Sellados eIDAS</span>
                <p class="text-xl sm:text-2xl font-bold text-emerald-400 mt-0.5">{{ $counts['firmado'] }}</p>
                <span class="text-[10px] text-emerald-300">Con evidencias</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">En Firma / Pendientes</span>
                <p class="text-xl sm:text-2xl font-bold text-teal-300 mt-0.5">{{ $counts['en_firma'] }}</p>
                <span class="text-[10px] text-slate-400">Esperando rúbrica</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">En Revisión</span>
                <p class="text-xl sm:text-2xl font-bold text-blue-400 mt-0.5">{{ $counts['en_revision'] }}</p>
                <span class="text-[10px] text-slate-400">Negociación activa</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 col-span-2 sm:col-span-1">
                <span class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Créditos Extra</span>
                <p class="text-xl sm:text-2xl font-bold text-amber-400 mt-0.5">{{ $user->credits }}</p>
                <span class="text-[10px] text-slate-400">Sellos sueltos</span>
            </div>
        </div>
    </div>

    {{-- Profile Completion Progress Meter --}}
    @if(!$user->isProfileComplete())
        <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-5 sm:p-6 shadow-xl space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-emerald-950/70 border border-emerald-800 flex items-center justify-center text-lg shrink-0">
                        🚀
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-white">Completa tu perfil para autocompletar tus contratos</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ $user->profileCompletionPercentage() }}% completado
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Añade tus datos fiscales para que se rellenen solos en 1 clic cada vez que redactes un contrato.
                        </p>
                    </div>
                </div>

                <a href="{{ route('profile.edit', ['tab' => 'identity']) }}" class="btn-primary text-xs px-5 py-2.5 font-bold shrink-0 shadow-md">
                    Completar mi perfil ahora →
                </a>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-slate-950 rounded-full h-2.5 overflow-hidden border border-slate-800">
                <div class="bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-400 h-2.5 rounded-full transition-all duration-500" style="width: {{ $user->profileCompletionPercentage() }}%"></div>
            </div>

            {{-- Checklist Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-1">
                @foreach($user->profileChecklist() as $key => $item)
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-semibold border transition
                        {{ $item['done'] ? 'bg-emerald-950/40 border-emerald-800 text-emerald-300' : 'bg-slate-950/60 border-slate-800 text-slate-400' }}">
                        <span>{{ $item['done'] ? '✓' : $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Status Filter Tabs & Search Bar --}}
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
        {{-- Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
            <a href="{{ route('dashboard') }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ empty($filters['status']) ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>Todos</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['all'] }}</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'borrador']) }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ $filters['status'] === 'borrador' ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>Borradores</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['borrador'] }}</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'en_revision']) }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ $filters['status'] === 'en_revision' ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>En Revisión</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['en_revision'] }}</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'en_firma']) }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ $filters['status'] === 'en_firma' ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>En Firma</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['en_firma'] }}</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'firmado']) }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ $filters['status'] === 'firmado' ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>Firmados & Sellados</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['firmado'] }}</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'cancelado']) }}"
               class="px-3.5 py-2 rounded-xl border transition flex items-center gap-2 shrink-0 {{ $filters['status'] === 'cancelado' ? 'bg-emerald-950/70 border-emerald-500 text-emerald-300 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                <span>Cancelados</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">{{ $counts['cancelado'] }}</span>
            </a>
        </div>

        {{-- Search & Type Filter Row --}}
        <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            @if(!empty($filters['status']))
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar por referencia, título, objeto..."
                       class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <select name="type" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los tipos de contrato</option>
                    @foreach(\App\Models\Contract::TYPES as $t)
                        <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ str_replace('_', ' ', ucfirst($t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-5 py-2.5 font-bold flex-1">
                    🔍 Filtrar
                </button>
                @if(!empty($filters['search']) || !empty($filters['type']) || !empty($filters['status']))
                    <a href="{{ route('dashboard') }}" class="btn-outline text-xs px-3.5 py-2.5 flex items-center justify-center text-slate-400 hover:text-white" title="Limpiar filtros">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Contracts List / Table --}}
    @if($contracts->count() === 0)
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-10 text-center space-y-4 shadow-xl">
            <div class="w-16 h-16 rounded-2xl bg-emerald-950/60 border border-emerald-800 flex items-center justify-center text-3xl mx-auto">
                📜
            </div>
            <h3 class="text-lg font-bold text-white">No se encontraron contratos con estos criterios</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Redacta un nuevo contrato o ajusta los filtros de búsqueda para localizar tus documentos.
            </p>
            <div class="pt-2">
                <a href="{{ route('contracts.create') }}" class="btn-primary inline-flex items-center gap-2 px-6 py-3 text-xs font-bold shadow-lg shadow-emerald-500/20">
                    <span>+</span> Redactar mi primer contrato
                </a>
            </div>
        </div>
    @else
        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                        <tr>
                            <th class="p-4">Referencia / Título</th>
                            <th class="p-4">Tipo & Régimen</th>
                            <th class="p-4">Partes Intervinientes</th>
                            <th class="p-4">Importe</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($contracts as $contract)
                            <tr class="hover:bg-slate-800/40 transition">
                                {{-- Reference & Title --}}
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-emerald-400 font-bold">{{ $contract->reference }}</span>
                                    </div>
                                    <a href="{{ route('contracts.show', $contract) }}" class="font-bold text-slate-100 text-sm hover:text-emerald-300 block mt-0.5">
                                        {{ $contract->title }}
                                    </a>
                                    <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $contract->object_description }}</span>
                                </td>

                                {{-- Type & Regime --}}
                                <td class="p-4">
                                    <span class="font-semibold text-slate-200 capitalize block">{{ str_replace('_', ' ', $contract->contract_type) }}</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        {{ strtoupper($contract->transaction_type) }} · {{ $contract->city }}
                                    </span>
                                </td>

                                {{-- Parties --}}
                                <td class="p-4">
                                    <div class="space-y-0.5 text-[11px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 font-medium">Vendedor:</span>
                                            <span class="text-slate-200 font-semibold truncate max-w-[140px]">{{ $contract->seller()?->displayName() ?? '-' }}</span>
                                            @if($contract->partyHasSigned('vendedor'))
                                                <span class="text-emerald-400 font-bold" title="Firmado">✓</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 font-medium">Comprador:</span>
                                            <span class="text-slate-200 font-semibold truncate max-w-[140px]">{{ $contract->buyer()?->displayName() ?? '-' }}</span>
                                            @if($contract->partyHasSigned('comprador'))
                                                <span class="text-emerald-400 font-bold" title="Firmado">✓</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Price --}}
                                <td class="p-4">
                                    <span class="font-bold text-slate-100 text-sm block">
                                        {{ number_format((float) $contract->total_amount, 2, ',', '.') }} {{ $contract->currency }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Total pactado</span>
                                </td>

                                {{-- Status --}}
                                <td class="p-4">
                                    @include('contracts._status_badge', ['status' => $contract->status])
                                </td>

                                {{-- Actions --}}
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('contracts.show', $contract) }}" class="btn-outline text-xs px-3 py-1.5 font-semibold hover:border-emerald-500 hover:text-emerald-300">
                                            Gestionar ↗
                                        </a>
                                        @if($contract->status === 'firmado' && $contract->final_pdf_path)
                                            <a href="{{ route('contracts.download', $contract) }}" class="p-1.5 rounded-lg bg-emerald-950/60 border border-emerald-800 text-emerald-300 hover:bg-emerald-900 text-xs" title="Descargar PDF">
                                                📥
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
