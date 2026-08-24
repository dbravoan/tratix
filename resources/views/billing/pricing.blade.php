@extends('layouts.app')

@section('title', 'Planes')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Elige tu plan</h1>
            <p class="text-sm text-slate-500">Este mes llevas {{ $used }} contrato(s). Plan actual: <strong class="text-emerald-400">{{ ucfirst($currentPlan) }}</strong>.</p>
            @if($credits > 0)
                <p class="text-sm text-emerald-300 mt-1">Tienes <strong>{{ $credits }}</strong> crédito(s) para sellos/verificaciones extra.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        @foreach($plans as $key => $plan)
            <div class="bg-slate-800 rounded-lg shadow p-6 border-t-4 {{ $key === 'pro' ? 'border-emerald-600' : 'border-slate-600' }}">
                <h2 class="font-bold text-lg text-slate-100">{{ $plan['name'] }}</h2>
                <p class="text-3xl font-bold text-slate-100 mt-2">
                    @if($plan['price_monthly'] > 0)
                        {{ $plan['price_monthly'] }} €<span class="text-sm font-normal text-slate-500">/mes</span>
                    @else
                        Gratis
                    @endif
                </p>
                <ul class="mt-4 space-y-2 text-sm text-slate-400">
                    @foreach($plan['features'] ?? [] as $feature)
                        <li>• {{ $feature }}</li>
                    @endforeach
                </ul>

                @if($currentPlan === $key)
                    <button disabled class="mt-6 w-full bg-gray-200 text-slate-500 font-semibold px-4 py-2.5 rounded-md cursor-not-allowed">
                        Plan actual
                    </button>
                @elseif($key === 'free')
                    <a href="{{ route('dashboard') }}" class="mt-6 block text-center bg-slate-800 hover:bg-slate-600 text-slate-300 font-semibold px-4 py-2.5 rounded-md">
                        Seguir con el plan gratis
                    </a>
                @else
                    <form method="POST" action="{{ route('billing.checkout') }}">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $key }}">
                        <button class="mt-6 w-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold px-4 py-2.5 rounded-md">
                            Pasarse a {{ $plan['name'] }}
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    @if($gateway === 'demo')
        <p class="text-center text-xs text-gray-400 mt-6">
            Modo demostración: el pago no se procesa. Configura <code>BILLING_GATEWAY=stripe</code> y las claves
            de Stripe para activar el cobro real.
        </p>
    @endif
@endsection