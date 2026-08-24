@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-600 bg-slate-900 text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/40 rounded-md shadow-sm text-sm px-3 py-2 transition']) }}>
