@php
    $proposals = $contract->proposals()->latest()->get();
    $comments = $contract->comments()->latest()->get();
    $clauses = $contract->clauses ?? [];
@endphp

<section class="bg-slate-800 rounded-xl shadow-lg border border-slate-700/80 p-5 space-y-6" x-data="{ tab: 'comments' }">
    {{-- Header with Tabs --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700 pb-3">
        <div class="flex items-center gap-2">
            <span class="text-lg">💬</span>
            <div>
                <h2 class="font-bold text-slate-100 text-sm">Interacción y Negociación entre Partes</h2>
                <p class="text-[11px] text-slate-400">Comentarios, dudas y propuestas de cambio sobre el documento.</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 p-1 bg-slate-900/80 rounded-lg border border-slate-700">
            <button type="button" @click="tab = 'comments'"
                class="px-3 py-1 text-xs font-semibold rounded-md transition"
                :class="tab === 'comments' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                Comentarios ({{ $comments->count() }})
            </button>
            <button type="button" @click="tab = 'proposals'"
                class="px-3 py-1 text-xs font-semibold rounded-md transition"
                :class="tab === 'proposals' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                Propuestas de Texto ({{ $proposals->count() }})
            </button>
        </div>
    </div>

    {{-- TAB 1: Comentarios y Notas --}}
    <div x-show="tab === 'comments'" class="space-y-4">
        {{-- Add Comment Form --}}
        @if(in_array($contract->status, ['borrador', 'en_revision'], true))
            <form method="POST" action="{{ route('contracts.comments.store', $contract) }}" class="bg-slate-900/90 border border-slate-700 p-4 rounded-xl space-y-3">
                @csrf
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-200">Añadir comentario o nota sobre una parte/cláusula</label>
                    <span class="text-[10px] text-slate-400">Visible para el comprador y vendedor</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <select name="clause_key" id="comment_clause_select" class="w-full border border-slate-600 bg-slate-800 text-slate-100 rounded-md px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            <option value="">General sobre el contrato</option>
                            @foreach($clauses as $clause)
                                <option value="{{ $clause['key'] }}">{{ $clause['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <textarea name="content" rows="2" class="w-full border border-slate-600 bg-slate-800 text-slate-100 rounded-md px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="Escribe una observación, sugerencia o acuerdo pactado con la otra parte…" required></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 flex items-center gap-1.5 shadow-sm">
                        <span>💬 Publicar comentario</span>
                    </button>
                </div>
            </form>
        @endif

        {{-- Comments List --}}
        <div class="space-y-3">
            @forelse($comments as $comment)
                <div class="p-3.5 rounded-xl border border-slate-700/80 bg-slate-900/60 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-200">{{ $comment->author_name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $comment->author_role === 'vendedor' ? 'bg-blue-950/60 text-blue-300 border border-blue-800' : 'bg-emerald-950/60 text-emerald-300 border border-emerald-800' }}">
                                {{ $comment->author_role }}
                            </span>
                            @if($comment->clause_key)
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                    📌 Cláusula: {{ $comment->clause_key }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $comment->content }}</p>
                </div>
            @empty
                <div class="text-center py-6 border border-dashed border-slate-700/60 rounded-xl bg-slate-900/30">
                    <p class="text-xs text-slate-400">No hay comentarios registrados todavía.</p>
                    <p class="text-[11px] text-slate-500 mt-1">Usa los comentarios para negociar detalles, acuerdos de plazos o resolver dudas antes de firmar.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: Propuestas de Redacción --}}
    <div x-show="tab === 'proposals'" class="space-y-4" style="display: none;">
        @if(in_array($contract->status, ['borrador', 'en_revision'], true) && count($clauses) > 0)
            <details class="bg-slate-900/90 border border-slate-700 p-4 rounded-xl group">
                <summary class="cursor-pointer text-xs font-bold text-emerald-400 group-hover:text-emerald-300 transition flex items-center justify-between">
                    <span>+ Proponer una modificación de texto formal en una cláusula</span>
                    <span class="text-slate-500 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <form method="POST" action="{{ route('contracts.proposals.store', $contract) }}" class="mt-3 space-y-3 pt-3 border-t border-slate-800">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Cláusula a modificar</label>
                        <select name="clause_key" class="w-full border border-slate-600 bg-slate-800 text-slate-100 rounded-md px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            @foreach($clauses as $clause)
                                <option value="{{ $clause['key'] }}">{{ $clause['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Nueva redacción propuesta</label>
                        <textarea name="proposed_text" rows="3" class="w-full border border-slate-600 bg-slate-800 text-slate-100 rounded-md px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="Redacta la modificación propuesta..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">Motivo o justificación (opcional)</label>
                        <input name="reason" class="w-full border border-slate-600 bg-slate-800 text-slate-100 rounded-md px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="Razón del cambio...">
                    </div>
                    <button class="btn-primary text-xs font-semibold px-4 py-2 shadow-sm">Registrar propuesta de cambio</button>
                </form>
            </details>
        @endif

        <div class="space-y-3">
            @forelse($proposals as $proposal)
                <div class="border border-slate-700 rounded-xl overflow-hidden bg-slate-900">
                    <div class="flex items-center justify-between px-4 py-2.5 text-xs bg-slate-800/80 border-b border-slate-700">
                        <span class="font-bold text-slate-200">{{ $proposal->clause_title }}</span>
                        <span class="text-[11px] text-slate-400">por <strong>{{ $proposal->proposed_by }}</strong> ·
                            <span class="font-bold {{ $proposal->status === 'pending' ? 'text-amber-400' : ($proposal->status === 'approved' ? 'text-emerald-400' : 'text-rose-400') }}">
                                {{ strtoupper($proposal->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="p-4 space-y-2.5 text-xs">
                        <div class="bg-rose-950/40 border-l-4 border-rose-500 p-2.5 rounded-r-md text-rose-200">
                            <span class="font-semibold text-rose-300 block mb-0.5">Texto actual:</span> {{ $proposal->original_text }}
                        </div>
                        <div class="bg-emerald-950/40 border-l-4 border-emerald-500 p-2.5 rounded-r-md text-emerald-200">
                            <span class="font-semibold text-emerald-300 block mb-0.5">Propuesta:</span> {{ $proposal->proposed_text }}
                        </div>
                        @if($proposal->reason)
                            <p class="text-[11px] text-slate-400"><strong>Motivo:</strong> {{ $proposal->reason }}</p>
                        @endif
                        @if($proposal->status === 'pending' && in_array($contract->status, ['borrador', 'en_revision'], true))
                            <div class="flex gap-2 pt-2 border-t border-slate-800 mt-2">
                                <form method="POST" action="{{ route('contracts.proposals.approve', [$contract, $proposal]) }}">
                                    @csrf
                                    <button class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">✓ Aprobar y aplicar</button>
                                </form>
                                <form method="POST" action="{{ route('contracts.proposals.reject', [$contract, $proposal]) }}">
                                    @csrf
                                    <button class="bg-rose-600 hover:bg-rose-500 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">✗ Rechazar</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 border border-dashed border-slate-700/60 rounded-xl bg-slate-900/30">
                    <p class="text-xs text-slate-400">No hay propuestas de modificación formal pendientes.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
