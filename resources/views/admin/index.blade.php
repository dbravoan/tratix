@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    <h1 class="text-2xl font-bold text-slate-100 mb-6">Panel de administración</h1>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            'Usuarios' => $stats['users'],
            'Contratos' => $stats['contracts'],
            'Firmados' => $stats['signed'],
            'Firmas' => $stats['signatures'],
            'Pro' => $stats['pro_users'],
        ] as $label => $value)
            <div class="bg-slate-800 rounded-lg shadow p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ $label }}</p>
                <p class="text-2xl font-bold text-slate-100 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-800 rounded-lg shadow p-6">
            <h3 class="font-semibold text-slate-100 border-b border-slate-700 pb-2 mb-3">Contratos por estado</h3>
            <ul class="space-y-2 text-sm">
                @foreach($byStatus as $status => $count)
                    <li class="flex justify-between">
                        <span class="text-slate-400">{{ $status }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-slate-800 rounded-lg shadow p-6">
            <h3 class="font-semibold text-slate-100 border-b border-slate-700 pb-2 mb-3">Contratos recientes</h3>
            <ul class="space-y-2 text-sm">
                @forelse($recentContracts as $contract)
                    <li class="flex justify-between gap-2">
                        <span class="font-mono text-emerald-400 text-xs truncate">{{ $contract->reference }}</span>
                        <span class="text-slate-500 truncate">{{ $contract->user?->email }}</span>
                        <span class="text-gray-400 text-xs">{{ $contract->status }}</span>
                    </li>
                @empty
                    <li class="text-gray-400">Sin contratos todavía.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection