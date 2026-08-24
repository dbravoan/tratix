@extends('layouts.app')

@section('title', 'Referir y ganar')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Referir y ganar</h1>
            <p class="text-sm text-slate-400">Comparte tu enlace. Cuando alguien se registre, ambos ganáis créditos y 1 mes de Pro gratis.</p>
        </div>
    </div>

    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-slate-100 mb-2">Tu enlace de referido</h2>
        <div class="flex flex-col sm:flex-row gap-3">
            <input readonly value="{{ $link }}" class="input flex-1 font-mono text-sm">
            <button type="button" onclick="navigator.clipboard.writeText('{{ $link }}'); this.textContent='✓ Copiado';"
                class="btn-primary">Copiar enlace</button>
        </div>
        <p class="text-sm text-slate-400 mt-3">
            Tus referidos reciben <strong>1 mes de Pro gratis</strong> y <strong>1 crédito</strong>. Tú ganas lo mismo por cada registro válido.
        </p>
    </div>

    <div class="card p-6">
        <h2 class="font-semibold text-slate-100 border-b border-slate-700 pb-2 mb-4">Tus referidos ({{ $referredCount }})</h2>
        @if($referrals->count())
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-700/40 text-left text-slate-400">
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Código</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referrals as $ref)
                        <tr class="border-t border-slate-700">
                            <td class="px-3 py-2 text-slate-300">{{ $ref->referred->email }}</td>
                            <td class="px-3 py-2 text-slate-400">{{ $ref->created_at->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-400">{{ $ref->code }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-slate-400 text-sm">Todavía no tienes referidos. ¡Comparte tu enlace!</p>
        @endif
    </div>
@endsection