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
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Expediente Documental y Trámites Oficiales</h1>
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
        $extraDocs = $extraDocuments ?? collect();
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
                        $docs = $item['documents'] ?? collect([$item['document']])->filter();
                    @endphp
                    <div class="bg-slate-900 border {{ $item['uploaded'] ? 'border-emerald-500/50 bg-emerald-950/10' : 'border-rose-900/60 bg-rose-950/10' }} rounded-2xl p-5 shadow-lg transition-all space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $item['uploaded'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                                        {{ $item['uploaded'] ? '✓ Adjuntado ('.$docs->count().')' : '● Requerido' }}
                                    </span>
                                    <h3 class="text-base font-bold text-white">{{ $req->title }}</h3>
                                    @if($item['validated'])
                                        <span class="text-[11px] bg-emerald-900/60 text-emerald-300 border border-emerald-700 px-2 py-0.5 rounded-full font-semibold">✓ Verificado</span>
                                    @elseif($item['uploaded'])
                                        <span class="text-[11px] bg-amber-950/80 text-amber-300 border border-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳ Pendiente de comprobación</span>
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
                            <div class="w-full lg:w-80 shrink-0 p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 space-y-3">
                                @if($docs->isNotEmpty())
                                    <div class="space-y-2">
                                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Archivos adjuntos ({{ $docs->count() }}):</span>
                                        @foreach($docs as $doc)
                                            <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 space-y-2">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-base">📄</span>
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-xs font-bold text-white block truncate" title="{{ $doc->filename }}">{{ $doc->filename }}</span>
                                                        <span class="text-[10px] text-slate-400 block">{{ number_format($doc->size / 1024, 1) }} KB · {{ $doc->uploaded_at?->diffForHumans() }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1.5 pt-1 border-t border-slate-800">
                                                    <a href="{{ route('contracts.documents.download', [$contract, $doc]) }}" class="btn-primary text-[10px] py-1 px-2.5 flex-1 text-center font-bold">
                                                        📥 Descargar
                                                    </a>
                                                    @if($doc->status !== 'validated')
                                                        <form method="POST" action="{{ route('contracts.documents.validate', [$contract, $doc]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn-outline text-[10px] py-1 px-2 hover:border-emerald-500 hover:text-emerald-300" title="Marcar como comprobado">
                                                                ✓
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-emerald-400 font-bold px-1.5 py-0.5 bg-emerald-950 rounded border border-emerald-800">✓ OK</span>
                                                    @endif
                                                    <form method="POST" action="{{ route('contracts.documents.destroy', [$contract, $doc]) }}" onsubmit="return confirm('¿Deseas eliminar este documento adjunto?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 text-rose-400 hover:text-rose-300 hover:bg-rose-950/60 rounded transition" title="Eliminar archivo">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Inline Upload More Files Form --}}
                                <form method="POST" action="{{ route('contracts.documents.upload', $contract) }}" enctype="multipart/form-data" class="space-y-2 pt-1 border-t border-slate-800/80">
                                    @csrf
                                    <input type="hidden" name="requirement_key" value="{{ $req->key }}">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">{{ $docs->isNotEmpty() ? '+ Adjuntar otro archivo a este trámite' : 'Subir archivo (PDF o Imagen)' }}</label>
                                        <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg,.webp" required
                                            class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-emerald-950 file:text-emerald-300 hover:file:bg-emerald-900 border border-slate-800 bg-slate-900 rounded">
                                    </div>
                                    <button type="submit" class="btn-outline w-full text-[11px] py-1.5 font-bold hover:border-emerald-500 hover:text-emerald-300">
                                        📎 Adjuntar
                                    </button>
                                </form>
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
                        $docs = $item['documents'] ?? collect([$item['document']])->filter();
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
                                        <span class="text-[11px] bg-amber-950/80 text-amber-300 border border-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳ Pendiente de comprobación</span>
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
                            <div class="w-full lg:w-80 shrink-0 p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 space-y-3">
                                @if($docs->isNotEmpty())
                                    <div class="space-y-2">
                                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Archivos adjuntos ({{ $docs->count() }}):</span>
                                        @foreach($docs as $doc)
                                            <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 space-y-2">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-base">📄</span>
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-xs font-bold text-white block truncate" title="{{ $doc->filename }}">{{ $doc->filename }}</span>
                                                        <span class="text-[10px] text-slate-400 block">{{ number_format($doc->size / 1024, 1) }} KB · {{ $doc->uploaded_at?->diffForHumans() }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1.5 pt-1 border-t border-slate-800">
                                                    <a href="{{ route('contracts.documents.download', [$contract, $doc]) }}" class="btn-primary text-[10px] py-1 px-2.5 flex-1 text-center font-bold">
                                                        📥 Descargar
                                                    </a>
                                                    @if($doc->status !== 'validated')
                                                        <form method="POST" action="{{ route('contracts.documents.validate', [$contract, $doc]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn-outline text-[10px] py-1 px-2 hover:border-emerald-500 hover:text-emerald-300">
                                                                ✓
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-emerald-400 font-bold px-1.5 py-0.5 bg-emerald-950 rounded border border-emerald-800">✓ OK</span>
                                                    @endif
                                                    <form method="POST" action="{{ route('contracts.documents.destroy', [$contract, $doc]) }}" onsubmit="return confirm('¿Deseas eliminar este documento adjunto?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 text-rose-400 hover:text-rose-300 hover:bg-rose-950/60 rounded transition">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Inline Upload More Files Form --}}
                                <form method="POST" action="{{ route('contracts.documents.upload', $contract) }}" enctype="multipart/form-data" class="space-y-2 pt-1 border-t border-slate-800/80">
                                    @csrf
                                    <input type="hidden" name="requirement_key" value="{{ $req->key }}">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">{{ $docs->isNotEmpty() ? '+ Adjuntar otro archivo a este trámite' : 'Subir archivo (PDF o Imagen)' }}</label>
                                        <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg,.webp" required
                                            class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 border border-slate-800 bg-slate-900 rounded">
                                    </div>
                                    <button type="submit" class="btn-outline w-full text-[11px] py-1.5 font-bold hover:border-emerald-500 hover:text-emerald-300">
                                        📎 Adjuntar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Section 3: Additional & Custom Documents for Official Procedures --}}
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                <h2 class="text-base font-bold text-teal-300 uppercase tracking-wide">
                    3. Documentos Adicionales y Trámites Específicos ({{ $extraDocs->count() }})
                </h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- List of extra documents --}}
            <div class="lg:col-span-2 space-y-3">
                @if($extraDocs->isNotEmpty())
                    @foreach($extraDocs as $extraDoc)
                        <div class="p-3.5 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-between gap-3 shadow">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-2xl">📎</span>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-white block truncate">{{ $extraDoc->filename }}</span>
                                    <span class="text-[10px] text-slate-400 block">
                                        Etiqueta: <strong class="text-slate-300">{{ str_replace('_', ' ', $extraDoc->requirement_key) }}</strong> · {{ number_format($extraDoc->size / 1024, 1) }} KB · Subido {{ $extraDoc->uploaded_at?->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('contracts.documents.download', [$contract, $extraDoc]) }}" class="btn-primary text-xs py-1.5 px-3 font-bold">
                                    📥 Descargar
                                </a>
                                <form method="POST" action="{{ route('contracts.documents.destroy', [$contract, $extraDoc]) }}" onsubmit="return confirm('¿Deseas eliminar este documento adjunto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/60 rounded-lg transition" title="Eliminar archivo">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-6 bg-slate-900/60 border border-dashed border-slate-800 rounded-xl text-center space-y-1">
                        <span class="text-2xl">📁</span>
                        <p class="text-xs font-semibold text-slate-300">No hay documentos adicionales adjuntos</p>
                        <p class="text-[11px] text-slate-500">Puedes adjuntar facturas, poderes notariales, recibos de IBI, liquidaciones de impuestos o informes periciales.</p>
                    </div>
                @endif
            </div>

            {{-- Upload custom document box --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow space-y-3">
                <h3 class="text-xs font-bold text-white flex items-center gap-1.5">
                    <span>+</span> Adjuntar Documento Personalizado
                </h3>
                <form method="POST" action="{{ route('contracts.documents.upload', $contract) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Nombre o Tipo de trámite *</label>
                        <input type="text" name="custom_label" required placeholder="p. ej. Recibo IBI, Poder Notarial, Factura"
                            class="w-full text-xs bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1.5 text-slate-100 placeholder-slate-500 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Archivo (PDF o Imagen) *</label>
                        <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg,.webp" required
                            class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-emerald-950 file:text-emerald-300 hover:file:bg-emerald-900 border border-slate-800 bg-slate-950 rounded">
                    </div>
                    <button type="submit" class="btn-primary w-full text-xs py-2 font-bold shadow">
                        📎 Subir y adjuntar al expediente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
