@extends('layouts.app')

@section('title', $contract->reference)

@section('content')
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-100">{{ $contract->title }}</h1>
                @include('contracts._status_badge', ['status' => $contract->status])
            </div>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-mono text-emerald-400">{{ $contract->reference }}</span> ·
                {{ $contract->city }}, {{ $contract->signing_date->format('d/m/Y') }} ·
                régimen <span class="uppercase font-bold">{{ $contract->transaction_type }}</span> ·
                ámbito {{ $contract->jurisdiction }}
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if(in_array($contract->status, ['borrador', 'en_revision'], true))
                <a href="{{ route('contracts.edit', $contract) }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-semibold flex items-center gap-1.5 shadow-sm transition">
                    <span>✏️</span> Editar contrato
                </a>
            @endif
            @if($contract->status !== 'firmado')
                <a href="{{ route('contracts.preview', $contract) }}" target="_blank" class="bg-slate-800 border border-slate-600 hover:bg-slate-700/40 px-4 py-2 rounded-md text-sm font-medium">Vista previa</a>
            @endif
            @if(in_array($contract->status, ['borrador', 'en_revision', 'lista_para_firma', 'en_firma', 'firmado'], true) && $contract->access_token)
                @include('contracts._share_modal', ['contract' => $contract])
            @endif
            @if($contract->status === 'firmado')
                <a href="{{ route('contracts.download', $contract) }}" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded-md text-sm font-medium">Descargar PDF firmado</a>
                <a href="{{ route('contracts.evidence', $contract) }}" class="bg-slate-800 border border-slate-600 hover:bg-slate-700/40 px-4 py-2 rounded-md text-sm font-medium">Hoja de evidencias</a>
                <form method="POST" action="{{ route('contracts.verify', $contract) }}">
                    @csrf
                    <button class="bg-slate-800 border border-slate-600 hover:bg-slate-700/40 px-4 py-2 rounded-md text-sm font-medium">Verificar integridad</button>
                </form>
                <a href="{{ route('verify.public', $contract->reference) }}" target="_blank" class="bg-slate-800 border border-slate-600 hover:bg-slate-700/40 px-4 py-2 rounded-md text-sm font-medium">Enlace de verificación</a>
            @else
                <a href="{{ route('contracts.download', $contract) }}" class="bg-slate-800 border border-slate-600 hover:bg-slate-700/40 px-4 py-2 rounded-md text-sm font-medium">Descargar PDF</a>
            @endif
        </div>
    </div>

    {{-- Validation issues --}}
    @if(!empty($issues))
        <div class="mb-6 bg-slate-800 border border-slate-700 rounded-xl p-4">
            <h2 class="font-semibold mb-2 text-slate-200">Validación legal</h2>
            @foreach($issues as $issue)
                <div class="mb-1 px-3 py-2 rounded text-sm {{ $issue['level'] === 'error' ? 'bg-rose-950/50 text-rose-300 border border-rose-800' : 'bg-amber-950/50 text-amber-300 border border-amber-800' }}">
                    <strong>{{ ucfirst($issue['level']) }}:</strong> {{ $issue['message'] }}
                </div>
            @endforeach
        </div>
    @endif

    {{-- Workflow stepper --}}
    @include('contracts._workflow_bar', ['contract' => $contract])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Left column: workflow actions + documents + parties --}}
        <div class="lg:col-span-1 space-y-6">
            <section class="bg-slate-800 rounded-lg shadow p-5">
                <h2 class="font-semibold text-emerald-400 border-b border-slate-700 pb-2 mb-3">Siguiente paso</h2>
                @include('contracts._workflow_actions', ['contract' => $contract])
            </section>

            <section class="bg-slate-800 rounded-lg shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-emerald-400">Trámites y documentos</h2>
                    <a href="{{ route('contracts.documents', $contract) }}" class="text-sm text-emerald-400 hover:underline">Ver todos</a>
                </div>
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-400">Progreso</span>
                        <span class="font-semibold text-emerald-400">{{ $completeness['done'] }}/{{ $completeness['total'] }}</span>
                    </div>
                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-2 bg-emerald-500 rounded-full" style="width: {{ $completeness['percent'] }}%"></div>
                    </div>
                </div>
                <ul class="space-y-2 text-sm">
                    @foreach($checklist->take(6) as $item)
                        <li class="flex items-start gap-2">
                            @if($item['uploaded'])
                                <span class="text-emerald-400 font-bold">✓</span>
                            @else
                                <span class="text-slate-500 font-bold">○</span>
                            @endif
                            <span class="{{ $item['uploaded'] ? 'text-slate-400' : 'text-slate-200' }}">{{ $item['requirement']->title }}</span>
                        </li>
                    @endforeach
                    @if($checklist->count() > 6)
                        <li class="text-slate-500 text-xs">… y {{ $checklist->count() - 6 }} más</li>
                    @endif
                </ul>
            </section>

            <section class="bg-slate-800 rounded-lg shadow p-5">
                <h2 class="font-semibold text-emerald-400 border-b border-slate-700 pb-2 mb-3">Partes</h2>
                @foreach([$contract->seller(), $contract->buyer()] as $party)
                    <div class="mb-4">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $party->role === 'vendedor' ? 'bg-blue-950/60 text-blue-300 border border-blue-800' : 'bg-emerald-950/60 text-emerald-300 border border-emerald-800' }}">{{ ucfirst($party->role) }}</span>
                        <p class="font-medium mt-1 text-slate-100">{{ $party->displayName() }}</p>
                        <p class="text-sm text-slate-400">
                            {{ $party->party_type }} ·
                            {{ strtoupper($party->tax_id_country) !== 'ES' ? strtoupper($party->tax_id_country) . '-' : '' }}{{ strtoupper($party->tax_id) }}
                        </p>
                        <p class="text-sm text-slate-400">{{ $party->address }}, {{ $party->postal_code }} {{ $party->city }}</p>
                    </div>
                @endforeach
            </section>
        </div>

        {{-- Right column: document, signatures, proposals, audit --}}
        <div class="lg:col-span-2 space-y-6">
            @include('contracts._proposals', ['contract' => $contract])

            <section class="bg-slate-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-slate-200">Documento</h2>
                    @if($contract->latestVersion())
                        <span class="text-xs text-gray-400">Versión congelada v{{ $contract->latestVersion()->version }} · hash {{ substr($contract->latestVersion()->hash, 0, 12) }}…</span>
                    @endif
                </div>
                <p class="text-sm text-slate-300 mb-4"><strong>Objeto:</strong> {{ $contract->object_description }}</p>
                <div class="space-y-4">
                    @foreach(($contract->clauses ?? []) as $clause)
                        @php
                            $clauseKey = $clause['key'] ?? $clause['title'];
                            $clauseComments = $contract->comments->where('clause_key', $clauseKey);
                        @endphp
                        <div class="p-3.5 rounded-xl border border-slate-700/80 bg-slate-900/40 hover:border-slate-600 transition">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <h3 class="text-emerald-400 font-bold text-sm">{{ $clause['title'] }}</h3>
                                <div class="flex items-center gap-2">
                                    @if($clauseComments->isNotEmpty())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950/60 text-amber-300 border border-amber-800">
                                            💬 {{ $clauseComments->count() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <p class="text-sm text-justify text-slate-300 leading-relaxed">{{ $clause['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            @if($contract->signatures->isNotEmpty())
                <section class="bg-slate-800 rounded-lg shadow p-5">
                    <h2 class="font-semibold text-emerald-400 border-b border-slate-700 pb-2 mb-3">Firmas</h2>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-700/40 text-left text-slate-400">
                                <th class="px-3 py-2">Rol</th>
                                <th class="px-3 py-2">Firmante</th>
                                <th class="px-3 py-2">Fecha (UTC)</th>
                                <th class="px-3 py-2">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->signatures as $sig)
                                <tr class="border-t border-slate-700">
                                    <td class="px-3 py-2">{{ ucfirst($sig->party_role) }}</td>
                                    <td class="px-3 py-2">{{ $sig->signer_name }}</td>
                                    <td class="px-3 py-2">{{ $sig->signed_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $sig->ip ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($contract->final_hash)
                        <p class="mt-3 text-xs text-slate-500 font-mono break-all">SHA-256 sellado: {{ $contract->final_hash }}</p>
                    @endif
                </section>
            @endif

            <section class="bg-slate-800 rounded-lg shadow p-5">
                <h2 class="font-semibold text-emerald-400 border-b border-slate-700 pb-2 mb-3">Historial (traza de auditoría)</h2>
                <ul class="space-y-2 text-sm">
                    @forelse($contract->auditEvents()->latest('happened_at')->limit(10)->get() as $event)
                        <li class="flex items-start gap-2 text-slate-300">
                            <span class="text-gray-300 font-mono text-xs mt-0.5">{{ $event->happened_at->format('d/m/Y H:i') }}</span>
                            <span>
                                <span class="font-semibold">{{ $event->event }}</span>
                                @if($event->detail)<span class="text-slate-500"> — {{ $event->detail }}</span>@endif
                            </span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sin eventos registrados.</li>
                    @endforelse
                </ul>
            </section>

            <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('¿Eliminar este contrato?');">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:underline text-sm">Eliminar contrato</button>
            </form>
        </div>
    </div>
@endsection
