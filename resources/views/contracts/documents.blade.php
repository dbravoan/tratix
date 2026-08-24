@extends('layouts.app')

@section('title', 'Documentación y Trámites – ' . $contract->reference)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
        <div>
            <a href="{{ route('contracts.show', $contract) }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5 mb-1.5 transition">
                <span>←</span> Volver al contrato {{ $contract->reference }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Expediente Documental y Trámites</h1>
            <p class="text-xs text-slate-400 mt-1">
                Contrato: <span class="text-slate-200 font-semibold">{{ $contract->title }}</span> · Tipo: <span class="text-emerald-400 font-semibold">{{ strtoupper($contract->contract_type) }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-700 text-slate-300 text-xs font-semibold">
                <span>🔒 Custodia Segura Cifrada</span>
            </span>
        </div>
    </div>

    {{-- Progress Card --}}
    <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Progreso del Expediente Legal</span>
                <h2 class="text-lg font-bold text-white mt-0.5">
                    {{ $completeness['done'] }} de {{ $completeness['total'] }} requisitos completados
                </h2>
            </div>
            <div class="text-right">
                <span class="text-2xl font-black {{ $completeness['percent'] == 100 ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ $completeness['percent'] }}%
                </span>
                <span class="text-xs text-slate-400 block">{{ $completeness['percent'] == 100 ? 'Expediente 100% completo' : 'Faltan documentos obligatorios o recomendados' }}</span>
            </div>
        </div>

        <div class="h-3 bg-slate-950 rounded-full overflow-hidden border border-slate-800 p-0.5">
            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $completeness['percent'] }}%"></div>
        </div>
    </div>

    @php
        $mandatoryItems = collect($checklist)->filter(fn($item) => $item['requirement']->mandatory);
        $recommendedItems = collect($checklist)->filter(fn($item) => ! $item['requirement']->mandatory);
    @endphp

    {{-- Section 1: Mandatory Documents --}}
    @if($mandatoryItems->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center gap-2 border-b border-rose-900/40 pb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                <h2 class="text-base font-bold text-rose-300 uppercase tracking-wide">
                    1. Documentación Obligatoria por Ley ({{ $mandatoryItems->where('uploaded', true)->count() }}/{{ $mandatoryItems->count() }})
                </h2>
            </div>

            <div class="space-y-4">
                @foreach($mandatoryItems as $item)
                    @php 
                        $req = $item['requirement'];
                        $doc = $item['document'];
                    @endphp
                    <div class="bg-slate-900 border {{ $item['uploaded'] ? 'border-emerald-500/50 bg-emerald-950/10' : 'border-rose-900/60 bg-rose-950/10' }} rounded-2xl p-5 shadow-lg transition-all space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $item['uploaded'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                                        {{ $item['uploaded'] ? '✓ Adjuntado' : '● Requerido' }}
                                    </span>
                                    <h3 class="text-base font-bold text-white">{{ $req->title }}</h3>
                                    @if($item['validated'])
                                        <span class="text-[11px] bg-emerald-900/60 text-emerald-300 border border-emerald-700 px-2 py-0.5 rounded-full font-semibold">✓ Verificado</span>
                                    @elseif($item['uploaded'])
                                        <span class="text-[11px] bg-amber-950/80 text-amber-300 border border-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳ Pendiente de revisión</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-300 leading-relaxed">
                                    <strong class="text-emerald-400 font-semibold">¿Por qué es obligatorio?</strong> {{ $req->purpose }}
                                </p>

                                @if($req->steps)
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        <strong class="text-slate-300">Cómo conseguirlo:</strong> {{ $req->steps }}
                                    </p>
                                @endif

                                @if($req->legal_note)
                                    <p class="text-[11px] text-slate-400 italic">
                                        ⚖️ {{ $req->legal_note }}
                                    </p>
                                @endif

                                @if($req->link_url)
                                    <div class="pt-1">
                                        <a href="{{ $req->link_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 underline inline-flex items-center gap-1">
                                            <span>🔗 {{ $req->link_label ?? 'Tramitar o consultar online' }}</span>
                                            <span>↗</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- File Attachment Box --}}
                            <div class="w-full lg:w-72 shrink-0 p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 space-y-3">
                                @if($doc)
                                    <div class="space-y-2">
                                        <div class="flex items-start gap-2.5">
                                            <span class="text-xl">📄</span>
                                            <div class="flex-1 min-w-0">
                                                <span class="text-xs font-bold text-white block truncate">{{ $doc->filename }}</span>
                                                <span class="text-[10px] text-slate-400 block">{{ number_format($doc->size / 1024, 1) }} KB · Subido {{ $doc->uploaded_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-800">
                                            <a href="{{ route('contracts.documents.download', [$contract, $doc]) }}" class="btn-primary text-[11px] py-1.5 px-3 flex-1 text-center font-bold">
                                                📥 Descargar
                                            </a>
                                            @if(!$item['validated'])
                                                <form method="POST" action="{{ route('contracts.documents.validate', [$contract, $doc]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-outline text-[11px] py-1.5 px-2.5 hover:border-emerald-500 hover:text-emerald-300" title="Marcar como comprobado">
                                                        ✓ Validar
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('contracts.documents.destroy', [$contract, $doc]) }}" onsubmit="return confirm('¿Deseas eliminar este documento adjunto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/60 rounded-lg transition" title="Eliminar archivo">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('contracts.documents.upload', $contract) }}" enctype="multipart/form-data" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="requirement_key" value="{{ $req->key }}">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Subir archivo (PDF o Imagen)</label>
                                            <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg" required
                                                class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-emerald-950 file:text-emerald-300 hover:file:bg-emerald-900 border border-slate-800 bg-slate-900 rounded-lg">
                                        </div>
                                        <button type="submit" class="btn-primary w-full text-xs py-2 font-bold shadow-md">
                                            📎 Adjuntar documento
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Section 2: Recommended / Complementary Documents --}}
    @if($recommendedItems->isNotEmpty())
        <div class="space-y-4 pt-4">
            <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                <h2 class="text-base font-bold text-slate-300 uppercase tracking-wide">
                    2. Documentación Recomendada / Opcional ({{ $recommendedItems->where('uploaded', true)->count() }}/{{ $recommendedItems->count() }})
                </h2>
            </div>

            <div class="space-y-4">
                @foreach($recommendedItems as $item)
                    @php 
                        $req = $item['requirement'];
                        $doc = $item['document'];
                    @endphp
                    <div class="bg-slate-900 border {{ $item['uploaded'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-slate-800' }} rounded-2xl p-5 shadow-lg transition-all space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        Recomendado
                                    </span>
                                    <h3 class="text-base font-bold text-white">{{ $req->title }}</h3>
                                    @if($item['validated'])
                                        <span class="text-[11px] bg-emerald-900/60 text-emerald-300 border border-emerald-700 px-2 py-0.5 rounded-full font-semibold">✓ Verificado</span>
                                    @elseif($item['uploaded'])
                                        <span class="text-[11px] bg-amber-950/80 text-amber-300 border border-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳ Pendiente de revisión</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-300 leading-relaxed">
                                    <strong class="text-slate-200">Utilidad probatoria:</strong> {{ $req->purpose }}
                                </p>

                                @if($req->steps)
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        <strong>Cómo conseguirlo:</strong> {{ $req->steps }}
                                    </p>
                                @endif

                                @if($req->link_url)
                                    <div class="pt-1">
                                        <a href="{{ $req->link_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 underline inline-flex items-center gap-1">
                                            <span>🔗 {{ $req->link_label ?? 'Consultar online' }}</span>
                                            <span>↗</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- File Attachment Box --}}
                            <div class="w-full lg:w-72 shrink-0 p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 space-y-3">
                                @if($doc)
                                    <div class="space-y-2">
                                        <div class="flex items-start gap-2.5">
                                            <span class="text-xl">📄</span>
                                            <div class="flex-1 min-w-0">
                                                <span class="text-xs font-bold text-white block truncate">{{ $doc->filename }}</span>
                                                <span class="text-[10px] text-slate-400 block">{{ number_format($doc->size / 1024, 1) }} KB · Subido {{ $doc->uploaded_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-800">
                                            <a href="{{ route('contracts.documents.download', [$contract, $doc]) }}" class="btn-primary text-[11px] py-1.5 px-3 flex-1 text-center font-bold">
                                                📥 Descargar
                                            </a>
                                            @if(!$item['validated'])
                                                <form method="POST" action="{{ route('contracts.documents.validate', [$contract, $doc]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-outline text-[11px] py-1.5 px-2.5 hover:border-emerald-500 hover:text-emerald-300">
                                                        ✓ Validar
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('contracts.documents.destroy', [$contract, $doc]) }}" onsubmit="return confirm('¿Deseas eliminar este documento adjunto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/60 rounded-lg transition">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('contracts.documents.upload', $contract) }}" enctype="multipart/form-data" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="requirement_key" value="{{ $req->key }}">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Subir archivo (PDF o Imagen)</label>
                                            <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg" required
                                                class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 border border-slate-800 bg-slate-900 rounded-lg">
                                        </div>
                                        <button type="submit" class="btn-outline w-full text-xs py-2 font-bold hover:border-emerald-500 hover:text-emerald-300">
                                            📎 Adjuntar documento
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
