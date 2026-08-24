@php
    $steps = [
        'borrador' => 'Borrador',
        'en_revision' => 'Revisión',
        'lista_para_firma' => 'Acordado',
        'en_firma' => 'En firma',
        'firmado' => 'Sellado',
    ];
    $order = array_keys($steps);
    $current = $contract->status === 'cancelado' ? 'borrador' : $contract->status;
    $currentIndex = array_search($current, $order) ?: 0;
@endphp
<ol class="flex items-center w-full text-xs">
    @foreach($order as $i => $step)
        @php
            $done = $i < $currentIndex;
            $active = $i === $currentIndex;
            $label = $steps[$step];
        @endphp
        <li class="flex items-center flex-1">
            <div class="flex flex-col items-center w-full">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $done ? 'bg-emerald-500 text-white' : ($active ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-slate-500') }}">
                    {{ $done ? '✓' : $i + 1 }}
                </span>
                <span class="mt-1 {{ $active ? 'text-emerald-400 font-semibold' : 'text-slate-500' }}">{{ $label }}</span>
            </div>
            @if(!$loop->last)
                <div class="h-0.5 flex-1 mx-2 mb-5 {{ $i < $currentIndex ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
            @endif
        </li>
    @endforeach
    @if($contract->status === 'cancelado')
        <li class="flex items-center">
            <span class="w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center text-xs font-bold">✕</span>
        </li>
    @endif
</ol>
