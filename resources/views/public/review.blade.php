<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Revisión y Edición de Borrador – {{ $contract->reference }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 font-sans antialiased text-slate-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="bg-emerald-900/50 border border-emerald-500/80 text-emerald-200 px-5 py-4 rounded-2xl text-sm shadow-lg flex items-center gap-3">
                <span class="text-xl">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-900/50 border border-rose-500/80 text-rose-200 px-5 py-4 rounded-2xl text-sm shadow-lg flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-900/50 border border-rose-500/80 text-rose-200 px-5 py-4 rounded-2xl text-sm shadow-lg">
                <strong>Hay errores en el formulario:</strong>
                <ul class="list-disc ml-5 mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            Revisión Colaborativa
                        </span>
                        <span class="font-mono text-xs text-emerald-400 font-bold">{{ $contract->reference }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">{{ $contract->title }}</h1>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $contract->city }}, {{ $contract->signing_date?->format('d/m/Y') }} · Régimen <span class="uppercase font-bold text-slate-300">{{ $contract->transaction_type }}</span> ({{ $contract->jurisdiction }})
                    </p>
                </div>

                <a href="{{ route('review.download', $token) }}" target="_blank" class="btn-outline flex items-center gap-2 px-4 py-2 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-300 transition shrink-0">
                    <span>📄</span>
                    <span>Descargar borrador en PDF</span>
                </a>
                {{-- Clear Role & Status Banner --}}
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <span class="text-xs text-slate-400 font-medium">Estás revisando este contrato como la parte invitada:</span>
                        <h3 class="text-sm font-bold text-emerald-400">{{ $counterpartyParty?->displayName() ?? 'Destinatario' }} ({{ ucfirst($counterpartyRole) }})</h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <span class="px-3 py-1.5 rounded-lg bg-slate-900 text-slate-300 border border-slate-700 font-medium">
                        Parte creadora: <strong class="text-slate-100">{{ $creatorParty?->displayName() ?? 'Titular' }} ({{ ucfirst($creatorRole) }})</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Side-by-Side Parties Overview & Counterparty Fillable Form --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Column: Creator Party (Read-only / Protected) --}}
            <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-950/80 border border-blue-800 flex items-center justify-center text-blue-400 font-bold text-sm">
                            🛡️
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-white">Parte Creadora: {{ ucfirst($creatorRole) }}</h3>
                            <span class="text-[11px] text-blue-300 font-medium">✓ Datos fijados por el creador</span>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">Protegido</span>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Tipo de parte:</span>
                        <span class="font-semibold text-slate-200 capitalize">{{ $creatorParty?->party_type ?? 'Particular' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Nombre / Razón Social:</span>
                        <span class="font-semibold text-slate-100 text-sm">{{ $creatorParty?->displayName() ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">NIF / CIF / NIE:</span>
                        <span class="font-mono text-slate-200">{{ strtoupper($creatorParty?->tax_id_country ?? 'ES') }} · {{ strtoupper($creatorParty?->tax_id ?? '—') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Domicilio Legal:</span>
                        <span class="text-slate-200">{{ $creatorParty?->address ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Población:</span>
                        <span class="text-slate-200">{{ $creatorParty?->postal_code }} {{ $creatorParty?->city }} {{ $creatorParty?->province ? '('.$creatorParty->province.')' : '' }}</span>
                    </div>
                    @if($creatorParty?->email || $creatorParty?->phone)
                        <div class="pt-2 border-t border-slate-800/80">
                            <span class="text-slate-500 block text-[11px]">Contacto del creador:</span>
                            <span class="text-slate-300">{{ $creatorParty?->email ?? '' }} {{ $creatorParty?->phone ? '· '.$creatorParty->phone : '' }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-[11px] text-slate-400 flex items-start gap-2">
                    <span>🔒</span>
                    <span>Los datos del creador del contrato están verificados y protegidos para asegurar la integridad jurídica del acuerdo.</span>
                </div>
            </div>

            {{-- Right Column: Counterparty Form (Fillable / Editable for the Guest) --}}
            <div class="lg:col-span-7 bg-slate-900 border border-emerald-900/60 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5" x-data="{ openEdit: true }">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-950/80 border border-emerald-800 flex items-center justify-center text-emerald-400 font-bold text-sm">
                            🪪
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-white">Tus Datos de Identificación ({{ ucfirst($counterpartyRole) }})</h2>
                            @php
                                $isPending = blank($counterpartyParty?->tax_id) || in_array($counterpartyParty?->tax_id, ['00000000T', '000000000', 'PENDIENTE'], true) || blank($counterpartyParty?->address);
                            @endphp
                            @if($isPending)
                                <span class="text-[11px] text-amber-300 font-semibold flex items-center gap-1">
                                    <span>⚠️</span> Por favor completa tus datos fiscales para el contrato
                                </span>
                            @else
                                <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                                    <span>✓</span> Datos registrados en el borrador
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('review.party.update', $token) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="{{ $counterpartyRole }}">

                    {{-- ID Card Scanner for Counterparty --}}
                    <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-200 flex items-center gap-2">
                                <span>📷</span> Escáner de Documento (DNI / NIE / Pasaporte)
                            </span>
                            <span id="scan_status_badge" class="text-[10px] text-slate-500 font-medium">Recomendado para autocompletar</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="border border-dashed border-slate-800 rounded-xl p-3 bg-slate-900/40 flex flex-col justify-between" id="slot_front">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-semibold text-slate-200 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                                            1. Anverso (Cara delantera)
                                        </span>
                                        <span id="badge_front" class="text-[10px] text-slate-500 font-medium">Pendiente</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5 mb-2">Con foto, nombre y número de DNI/NIE.</p>

                                    {{-- Live Photo Preview --}}
                                    <div id="preview_front" class="hidden mb-2.5 p-2 rounded-lg bg-slate-950 border border-slate-800 flex items-center gap-2.5">
                                        <img id="thumb_front" src="" alt="Anverso DNI" class="h-12 w-16 object-cover rounded border border-slate-700 shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[11px] font-semibold text-emerald-400 block truncate" id="name_front">anverso.jpg</span>
                                            <span class="text-[10px] text-slate-400">✓ Listo para adjuntar</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-emerald-500 hover:text-emerald-300">
                                        <span>📷 Cámara</span>
                                        <input type="file" class="hidden js-id-scanner" data-role="{{ $counterpartyRole }}" data-side="front" accept="image/*" capture="environment">
                                    </label>
                                    <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-slate-500 text-slate-300">
                                        <span>📁 Archivo</span>
                                        <input type="file" class="hidden js-id-scanner" data-role="{{ $counterpartyRole }}" data-side="front" accept="image/*,.pdf">
                                    </label>
                                </div>
                            </div>

                            <div class="border border-dashed border-slate-800 rounded-xl p-3 bg-slate-900/40 flex flex-col justify-between" id="slot_back">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-semibold text-slate-200 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                                            2. Reverso (Cara trasera)
                                        </span>
                                        <span id="badge_back" class="text-[10px] text-slate-500 font-medium">Pendiente</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5 mb-2">Con domicilio, CP y líneas MRZ.</p>

                                    {{-- Live Photo Preview --}}
                                    <div id="preview_back" class="hidden mb-2.5 p-2 rounded-lg bg-slate-950 border border-slate-800 flex items-center gap-2.5">
                                        <img id="thumb_back" src="" alt="Reverso DNI" class="h-12 w-16 object-cover rounded border border-slate-700 shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[11px] font-semibold text-blue-400 block truncate" id="name_back">reverso.jpg</span>
                                            <span class="text-[10px] text-slate-400">✓ Listo para adjuntar</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-blue-500 hover:text-blue-300">
                                        <span>📷 Cámara</span>
                                        <input type="file" class="hidden js-id-scanner" data-role="{{ $counterpartyRole }}" data-side="back" accept="image/*" capture="environment">
                                    </label>
                                    <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-slate-500 text-slate-300">
                                        <span>📁 Archivo</span>
                                        <input type="file" class="hidden js-id-scanner" data-role="{{ $counterpartyRole }}" data-side="back" accept="image/*,.pdf">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="scan_status" class="hidden text-xs p-2.5 rounded-lg border"></div>
                        <input type="hidden" name="id_card_front_token" id="id_card_front_token" value="{{ old('id_card_front_token') }}">
                        <input type="hidden" name="id_card_back_token" id="id_card_back_token" value="{{ old('id_card_back_token') }}">
                        <input type="hidden" name="id_card_token" id="id_card_token" value="{{ old('id_card_token') }}">
                    </div>

                    {{-- Legal Fields Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">¿Cómo actúas en este contrato? *</label>
                            <select name="party_type" id="party_type" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="particular" @selected(old('party_type', $counterpartyParty?->party_type ?? 'particular') === 'particular')>👤 Persona Física / Particular</option>
                                <option value="autonomo" @selected(old('party_type', $counterpartyParty?->party_type) === 'autonomo')>💼 Autónomo / Profesional</option>
                                <option value="sociedad" @selected(old('party_type', $counterpartyParty?->party_type) === 'sociedad')>🏢 Sociedad / Empresa (SL, SA, etc.)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">País de Residencia Fiscal *</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $counterpartyParty?->country ?? 'ES') }}" maxlength="2" class="w-full uppercase border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div id="wrapper_company_name" style="{{ ($counterpartyParty?->party_type ?? 'particular') === 'particular' ? 'display:none' : '' }}">
                            <label class="block font-semibold text-slate-200 mb-1" id="label_company_name">
                                {{ ($counterpartyParty?->party_type ?? '') === 'autonomo' ? 'Nombre Comercial / Actividad *' : 'Razón Social (Sociedad SL, SA...) *' }}
                            </label>
                            <input type="text" name="company_name" id="input_company_name" value="{{ old('company_name', $counterpartyParty?->company_name) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="Nombre de la empresa o actividad">
                        </div>

                        <div id="wrapper_full_name">
                            <label class="block font-semibold text-slate-200 mb-1" id="label_full_name">
                                {{ ($counterpartyParty?->party_type ?? '') === 'sociedad' ? 'Representante Legal / Administrador (quien firma) *' : 'Nombre y Apellidos completos *' }}
                            </label>
                            <input type="text" name="full_name" id="input_full_name" value="{{ old('full_name', $counterpartyParty?->full_name) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="Como figura en tu DNI">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1" id="label_tax_id">
                                {{ ($counterpartyParty?->party_type ?? '') === 'sociedad' ? 'CIF de la Empresa *' : 'NIF / NIE / Documento de Identidad *' }}
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="tax_id_country" id="tax_id_country" value="{{ old('tax_id_country', $counterpartyParty?->tax_id_country ?? 'ES') }}" maxlength="2" class="w-14 uppercase text-center border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-2 py-2 text-xs focus:ring-2 focus:ring-emerald-500">
                                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $counterpartyParty?->tax_id) }}" class="flex-1 uppercase border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="12345678Z" required>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">Domicilio Fiscal / Dirección *</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $counterpartyParty?->address) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="Calle, número, piso..." required>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">Código Postal y Ciudad *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $counterpartyParty?->postal_code) }}" class="border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="28001" required>
                                <input type="text" name="city" id="city" value="{{ old('city', $counterpartyParty?->city) }}" class="col-span-2 border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="Madrid" required>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">Provincia</label>
                            <input type="text" name="province" id="province" value="{{ old('province', $counterpartyParty?->province) }}" class="border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="Madrid">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">Email de Contacto</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $counterpartyParty?->email) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="email@ejemplo.com">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-200 mb-1">Teléfono (WhatsApp)</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $counterpartyParty?->phone) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500" placeholder="+34 600 000 000">
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-gdpr-info-box title="Tratamiento de tus datos como parte contratante (RGPD)" />
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end">
                        <button type="submit" class="btn-primary text-xs px-6 py-2.5 font-bold shadow-lg shadow-emerald-500/20">
                            💾 Guardar y Actualizar mis datos en el contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Document Clauses Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">📜 Documento Contractual</h2>
            <p class="text-xs text-slate-300"><strong>Objeto del contrato:</strong> {{ $contract->object_description }}</p>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-2 bg-slate-950/60 p-4 rounded-2xl border border-slate-800 text-xs leading-relaxed text-slate-300">
                @foreach(($contract->latestVersion()?->clauses ?? $contract->clauses ?? []) as $clause)
                    <div class="pb-2 border-b border-slate-800/80 last:border-0">
                        <h3 class="text-emerald-400 font-bold mb-1">{{ $clause['title'] }}</h3>
                        <div class="text-justify text-slate-300 whitespace-pre-line leading-relaxed">{{ $clause['body'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rights and Obligations Summary --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">⚖️ Derechos y obligaciones de las partes</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <h3 class="font-bold text-slate-100 text-sm mb-2">Como VENDEDOR ({{ $seller?->displayName() }})</h3>
                    <p class="font-bold text-emerald-400 uppercase tracking-wide mb-1">Derechos</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300 mb-3">
                        @foreach($rights['vendedor']['rights'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                    <p class="font-bold text-rose-400 uppercase tracking-wide mb-1">Obligaciones</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300">
                        @foreach($rights['vendedor']['obligations'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                </div>
                <div class="p-3.5 bg-slate-950/60 border border-slate-800 rounded-xl">
                    <h3 class="font-bold text-slate-100 text-sm mb-2">Como COMPRADOR ({{ $buyer?->displayName() }})</h3>
                    <p class="font-bold text-emerald-400 uppercase tracking-wide mb-1">Derechos</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300 mb-3">
                        @foreach($rights['comprador']['rights'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                    <p class="font-bold text-rose-400 uppercase tracking-wide mb-1">Obligaciones</p>
                    <ul class="list-disc ml-4 space-y-1 text-slate-300">
                        @foreach($rights['comprador']['obligations'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Comments and Interaction Section --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">💬 Comentarios y observaciones</h2>
            <div class="space-y-3 mb-4">
                @forelse($contract->comments as $comment)
                    <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-950/60 flex flex-col gap-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-200">{{ $comment->author_name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $comment->author_role === 'vendedor' ? 'bg-blue-950/60 text-blue-300 border border-blue-800' : 'bg-emerald-950/60 text-emerald-300 border border-emerald-800' }}">
                                    {{ $comment->author_role }}
                                </span>
                                @if($comment->clause_key)
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700">
                                        📌 Cláusula: {{ $comment->clause_title ?? $comment->clause_key }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p class="text-slate-500 text-xs italic">Aún no hay comentarios en este contrato.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('review.comments.store', $token) }}" class="space-y-3 bg-slate-950/70 border border-slate-800 p-4 rounded-2xl text-xs">
                @csrf
                <input type="hidden" name="author_role" value="{{ $activeRole }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-200 mb-1">Tu nombre *</label>
                        <input name="author_name" value="{{ old('author_name', $activeParty?->displayName()) }}" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500" required>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-200 mb-1">Cláusula relacionada (opcional)</label>
                        <select name="clause_key" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                            <option value="">General sobre el contrato</option>
                            @foreach(($contract->latestVersion()?->clauses ?? $contract->clauses ?? []) as $clause)
                                <option value="{{ $clause['key'] }}">{{ $clause['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-slate-200 mb-1">Comentario o mensaje *</label>
                    <textarea name="content" rows="2" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500" placeholder="Escribe tu consulta o propuesta..." required></textarea>
                </div>
                <button class="btn-primary text-xs px-4 py-2 font-bold">Publicar comentario</button>
            </form>
        </div>

        {{-- Official Documents & Procedures Checklist for Counterparty --}}
        @if(isset($checklist) && $checklist->isNotEmpty())
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-teal-950/80 border border-teal-800 flex items-center justify-center text-teal-400 font-bold text-sm">
                            📁
                        </span>
                        <div>
                            <h2 class="font-bold text-teal-400 text-sm">Expediente y Documentación para Trámites Oficiales</h2>
                            <p class="text-xs text-slate-400">Documentos necesarios para gestiones oficiales (DGT, Notaría, Hacienda, etc.). Ambas partes pueden consultar y aportar documentación.</p>
                        </div>
                    </div>
                    @if(isset($completeness))
                        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-950 border border-slate-800 text-slate-300 font-semibold self-start sm:self-auto">
                            {{ $completeness['done'] }}/{{ $completeness['total'] }} completados
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach($checklist as $item)
                        @php 
                            $req = $item['requirement'];
                            $docs = $item['documents'] ?? collect([$item['document']])->filter();
                        @endphp
                        <div class="p-3.5 rounded-2xl bg-slate-950/70 border {{ $item['uploaded'] ? 'border-emerald-500/40' : 'border-slate-800' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold {{ $item['uploaded'] ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ $item['uploaded'] ? '✓' : '○' }} {{ $req->title }}
                                    </span>
                                    @if($req->mandatory)
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-rose-950 text-rose-300 border border-rose-800 font-bold">Obligatorio</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed">{{ $req->purpose }}</p>
                                
                                @if($docs->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 pt-1.5">
                                        @foreach($docs as $doc)
                                            <a href="{{ route('review.documents.download', [$token, $doc]) }}" class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-emerald-300 border border-emerald-800/60 font-semibold transition" title="Descargar {{ $doc->filename }}">
                                                <span>📥</span>
                                                <span class="truncate max-w-[180px]">{{ $doc->filename }}</span>
                                                <span class="text-[9px] text-slate-500">({{ number_format($doc->size / 1024, 1) }} KB)</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-800">
                                <form method="POST" action="{{ route('review.documents.upload', $token) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="requirement_key" value="{{ $req->key }}">
                                    <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-emerald-500 hover:text-emerald-300">
                                        <span>📎 Adjuntar</span>
                                        <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg,.webp" class="hidden" onchange="this.form.submit()">
                                    </label>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    @if(isset($extraDocuments) && $extraDocuments->isNotEmpty())
                        <div class="pt-2">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Otros documentos aportados:</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($extraDocuments as $extraDoc)
                                    <a href="{{ route('review.documents.download', [$token, $extraDoc]) }}" class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl bg-slate-950 hover:bg-slate-800 text-slate-200 border border-slate-800 font-semibold transition">
                                        <span>📄</span>
                                        <span>{{ $extraDoc->filename }}</span>
                                        <span class="text-[10px] text-emerald-400">📥</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Decision / Accept or Propose Changes --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4">
            <h2 class="font-bold text-emerald-400 text-sm border-b border-slate-800 pb-2">✅ Aceptación del Borrador</h2>
            <p class="text-xs text-slate-400">Si estás conforme con los datos y las cláusulas, confírmalo para que el creador pueda congelar la versión final y proceder a la firma.</p>

            <form method="POST" action="{{ route('review.accept', $token) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="role" value="{{ $activeRole }}">
                <div>
                    <label class="block text-xs font-medium text-slate-200 mb-1">Nombre del Aceptante *</label>
                    <input name="acceptor_name" value="{{ old('acceptor_name', $activeParty?->displayName()) }}" class="w-full border border-slate-700 bg-slate-950 text-slate-100 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-emerald-500" required>
                </div>
                <button class="btn-primary w-full py-3 text-sm font-bold shadow-lg shadow-emerald-500/20">
                    Acepto el borrador del contrato
                </button>
            </form>

            <div class="border-t border-slate-800 pt-3 text-xs">
                <details>
                    <summary class="cursor-pointer font-bold text-emerald-400 hover:text-emerald-300">Prefiero proponer una modificación de cláusula</summary>
                    <form method="POST" action="{{ route('review.propose', $token) }}" class="mt-3 space-y-3 bg-slate-950/70 border border-slate-800 p-4 rounded-2xl">
                        @csrf
                        <input type="hidden" name="role" value="{{ $activeRole }}">
                        <div>
                            <label class="block font-medium text-slate-200 mb-1">Cláusula a modificar</label>
                            <select name="clause_key" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                                @foreach(($contract->latestVersion()?->clauses ?? $contract->clauses ?? []) as $clause)
                                    <option value="{{ $clause['key'] }}">{{ $clause['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-slate-200 mb-1">Texto propuesto *</label>
                            <textarea name="proposed_text" rows="3" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500" required></textarea>
                        </div>
                        <div>
                            <label class="block font-medium text-slate-200 mb-1">Motivo</label>
                            <input name="reason" class="w-full border border-slate-700 bg-slate-900 text-slate-100 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <button class="btn-outline text-xs px-5 py-2 font-bold hover:border-emerald-500">Enviar propuesta de cambio</button>
                    </form>
                </details>
            </div>
        </div>
    </div>

    {{-- OCR Scanner Script for Review Screen with Client Tesseract.js --}}
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <script>
        // High-contrast Canvas pre-processing & Tesseract OCR
        async function preprocessAndOcrImage(file, onProgress) {
            if (typeof Tesseract === 'undefined' || file.type === 'application/pdf') {
                return null;
            }

            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const img = new Image();
                    img.onload = async () => {
                        try {
                            const canvas = document.createElement('canvas');
                            const maxDim = 1800;
                            let width = img.width;
                            let height = img.height;
                            if (width > maxDim || height > maxDim) {
                                if (width > height) {
                                    height = Math.round((height * maxDim) / width);
                                    width = maxDim;
                                } else {
                                    width = Math.round((width * maxDim) / height);
                                    height = maxDim;
                                }
                            }
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            // Contrast and grayscale normalization
                            const imgData = ctx.getImageData(0, 0, width, height);
                            const d = imgData.data;
                            for (let i = 0; i < d.length; i += 4) {
                                const gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
                                const contrast = 1.25;
                                const adjusted = Math.min(255, Math.max(0, (gray - 128) * contrast + 128));
                                d[i] = adjusted;
                                d[i + 1] = adjusted;
                                d[i + 2] = adjusted;
                            }
                            ctx.putImageData(imgData, 0, 0);

                            const worker = await Tesseract.createWorker('spa', 1, {
                                logger: (m) => {
                                    if (m.status === 'recognizing text' && onProgress) {
                                        onProgress(Math.round((m.progress || 0) * 100));
                                    }
                                }
                            });
                            const ret = await worker.recognize(canvas);
                            await worker.terminate();
                            resolve(ret.data.text || null);
                        } catch (err) {
                            console.warn('OCR error:', err);
                            resolve(null);
                        }
                    };
                    img.onerror = () => resolve(null);
                    img.src = e.target.result;
                };
                reader.onerror = () => resolve(null);
                reader.readAsDataURL(file);
            });
        }

        function highlightField(el) {
            if (!el) return;
            el.classList.add('ring-2', 'ring-emerald-400', 'bg-emerald-950/40');
            setTimeout(() => {
                el.classList.remove('ring-2', 'ring-emerald-400', 'bg-emerald-950/40');
            }, 2500);
        }

        // Helper for non-destructive autofill and suggestions
        function smartFillOrSuggest(inputEl, newValue, fieldLabel) {
            if (!inputEl || !newValue || !String(newValue).trim()) return { autoFilled: false, suggested: false };
            const currentVal = (inputEl.value || '').trim();
            const cleanNew = String(newValue).trim();

            const isPlaceholder = !currentVal || ['PENDIENTE', '00000', '00000000T', '000000000', 'Pendiente de cumplimentar', 'Pendiente'].includes(currentVal);
            const isEquivalent = currentVal.toLowerCase() === cleanNew.toLowerCase();

            if (isPlaceholder || isEquivalent) {
                inputEl.value = cleanNew;
                highlightField(inputEl);
                const oldPill = document.getElementById(`sugg_${inputEl.id}`);
                if (oldPill) oldPill.remove();
                return { autoFilled: true, suggested: false };
            }

            // Show suggestion pill without overwriting
            let pill = document.getElementById(`sugg_${inputEl.id}`);
            if (!pill) {
                pill = document.createElement('div');
                pill.id = `sugg_${inputEl.id}`;
                pill.className = 'mt-1.5 flex items-center justify-between gap-2 text-[11px] text-amber-200 bg-amber-950/70 border border-amber-800/80 rounded-lg px-2.5 py-1.5 shadow-sm transition';
                inputEl.parentNode.insertBefore(pill, inputEl.nextSibling);
            }

            pill.innerHTML = `
                <span class="truncate">💡 Detectado en DNI (${fieldLabel}): <strong>${cleanNew}</strong></span>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" class="text-xs text-emerald-400 font-semibold hover:underline" id="btn_apply_${inputEl.id}">Reemplazar</button>
                    <button type="button" class="text-xs text-slate-400 hover:text-slate-200" id="btn_dismiss_${inputEl.id}">✕</button>
                </div>
            `;

            document.getElementById(`btn_apply_${inputEl.id}`)?.addEventListener('click', () => {
                inputEl.value = cleanNew;
                highlightField(inputEl);
                pill.remove();
            });

            document.getElementById(`btn_dismiss_${inputEl.id}`)?.addEventListener('click', () => {
                pill.remove();
            });

            return { autoFilled: false, suggested: true };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const partyTypeSelect = document.getElementById('party_type');
            const fullNameWrap = document.getElementById('wrapper_full_name');
            const compNameWrap = document.getElementById('wrapper_company_name');

            if (partyTypeSelect) {
                const updateLabels = (type) => {
                    const isPart = type === 'particular';
                    const isAutonomo = type === 'autonomo';
                    const isSociedad = type === 'sociedad';

                    if (compNameWrap) compNameWrap.style.display = isPart ? 'none' : '';
                    if (fullNameWrap) fullNameWrap.style.display = '';

                    const lblComp = document.getElementById('label_company_name');
                    if (lblComp) {
                        lblComp.textContent = isAutonomo ? 'Nombre Comercial / Actividad *' : 'Razón Social (Sociedad SL, SA...) *';
                    }

                    const lblName = document.getElementById('label_full_name');
                    if (lblName) {
                        lblName.textContent = isSociedad ? 'Representante Legal / Administrador (quien firma) *' : (isAutonomo ? 'Nombre y Apellidos del profesional *' : 'Nombre y Apellidos completos *');
                    }

                    const lblTax = document.getElementById('label_tax_id');
                    if (lblTax) {
                        lblTax.textContent = isSociedad ? 'CIF de la Empresa *' : 'NIF / NIE / Documento de Identidad *';
                    }
                };

                partyTypeSelect.addEventListener('change', (e) => {
                    updateLabels(e.target.value);
                });
                updateLabels(partyTypeSelect.value);
            }

            document.querySelectorAll('.js-id-scanner').forEach(input => {
                input.addEventListener('change', async (e) => {
                    const file = e.target.files[0];
                    if (!file) return;

                    const side = input.getAttribute('data-side') || 'front';
                    const sideLabel = side === 'front' ? 'Anverso' : 'Reverso';
                    const statusEl = document.getElementById('scan_status');
                    const badgeEl = document.getElementById(`badge_${side}`);
                    const slotEl = document.getElementById(`slot_${side}`);
                    const previewEl = document.getElementById(`preview_${side}`);
                    const thumbEl = document.getElementById(`thumb_${side}`);
                    const nameEl = document.getElementById(`name_${side}`);

                    // Show thumbnail preview immediately
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (rev) => {
                            if (thumbEl) thumbEl.src = rev.target.result;
                            if (nameEl) nameEl.textContent = file.name;
                            if (previewEl) previewEl.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }

                    if (statusEl) {
                        statusEl.className = 'text-xs p-2.5 rounded-lg border bg-amber-950/60 border-amber-700 text-amber-200 block';
                        statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">🔍</span> Analizando <strong>${sideLabel}</strong> (${file.name}) con OCR...`;
                    }

                    // Run high-contrast client OCR with live progress
                    let ocrText = null;
                    try {
                        ocrText = await preprocessAndOcrImage(file, (pct) => {
                            if (statusEl) {
                                statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">🔍</span> Leyendo texto de <strong>${sideLabel}</strong>: ${pct}%...`;
                            }
                        });
                    } catch (ocrErr) {
                        console.log('Client OCR error, falling back to server', ocrErr);
                    }

                    if (statusEl) {
                        statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">⚡</span> Validando datos fiscales y extrayendo campos de <strong>${sideLabel}</strong>...`;
                    }

                    const formData = new FormData();
                    formData.append('document', file);
                    formData.append('side', side);
                    if (ocrText) {
                        formData.append('ocr_text', ocrText);
                    }
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const res = await fetch('{{ route("contracts.scan-id") }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        });
                        const data = await res.json();

                        if (data.scan_token) {
                            if (side === 'front') {
                                const frontToken = document.getElementById('id_card_front_token') || document.getElementById('input_id_card_front_token');
                                if (frontToken) frontToken.value = data.scan_token;
                            } else {
                                const backToken = document.getElementById('id_card_back_token') || document.getElementById('input_id_card_back_token');
                                if (backToken) backToken.value = data.scan_token;
                            }
                            const mainToken = document.getElementById('id_card_token') || document.getElementById('input_id_card_token');
                            if (mainToken && !mainToken.value) mainToken.value = data.scan_token;
                        }

                        if (badgeEl) {
                            badgeEl.className = 'text-[10px] text-emerald-400 font-semibold';
                            badgeEl.textContent = '✓ ' + sideLabel + ' cargado';
                        }
                        if (slotEl) {
                            slotEl.classList.remove('border-dashed', 'border-slate-800');
                            slotEl.classList.add('border-solid', 'border-emerald-500/80', 'bg-emerald-950/20');
                        }

                        if (data.success) {
                            let suggestionsCount = 0;

                            if (data.party_type && partyTypeSelect && partyTypeSelect.value !== data.party_type) {
                                partyTypeSelect.value = data.party_type;
                                partyTypeSelect.dispatchEvent(new Event('change'));
                            }

                            if (data.full_name) {
                                const nameEl = document.getElementById('full_name');
                                const res = smartFillOrSuggest(nameEl, data.full_name, 'Nombre');
                                if (res?.suggested) suggestionsCount++;
                            }

                            if (data.tax_id) {
                                const taxEl = document.getElementById('tax_id');
                                const res = smartFillOrSuggest(taxEl, data.tax_id, 'NIF/NIE');
                                if (res?.suggested) suggestionsCount++;
                            }

                            if (data.tax_id_country && document.getElementById('tax_id_country')) {
                                document.getElementById('tax_id_country').value = data.tax_id_country;
                            }

                            if (data.address) {
                                const addrEl = document.getElementById('address');
                                const res = smartFillOrSuggest(addrEl, data.address, 'Dirección');
                                if (res?.suggested) suggestionsCount++;
                            }

                            if (data.postal_code) {
                                const cpEl = document.getElementById('postal_code');
                                const res = smartFillOrSuggest(cpEl, data.postal_code, 'Código Postal');
                                if (res?.suggested) suggestionsCount++;
                            }

                            if (data.city) {
                                const cityEl = document.getElementById('city');
                                const res = smartFillOrSuggest(cityEl, data.city, 'Ciudad');
                                if (res?.suggested) suggestionsCount++;
                            }

                            if (statusEl) {
                                statusEl.className = 'text-xs p-2.5 rounded-lg border bg-emerald-950/60 border-emerald-700 text-emerald-200 block';
                                const info = [data.full_name, data.tax_id, data.city].filter(Boolean).join(' · ');
                                let msg = `<strong>✓ ${sideLabel} procesado con éxito:</strong> ${info || 'Foto lista para adjuntar'}. Se adjuntará al contrato.`;
                                if (suggestionsCount > 0) {
                                    msg += ` <span class="text-amber-300 ml-1">(${suggestionsCount} sugerencia(s) disponible(s) sin sobreescribir tus datos).</span>`;
                                }
                                statusEl.innerHTML = msg;
                            }
                        } else {
                            if (statusEl) {
                                statusEl.className = 'text-xs p-2.5 rounded-lg border bg-amber-950/60 border-amber-700 text-amber-200 block';
                                statusEl.innerHTML = `<strong>✓ ${sideLabel} adjuntado:</strong> La foto se adjuntará al expediente oficial del contrato. Puedes completar tus datos en los campos a continuación.`;
                            }
                        }
                    } catch (err) {
                        if (statusEl) {
                            statusEl.className = 'text-xs p-2.5 rounded-lg border bg-rose-950/60 border-rose-700 text-rose-200 block';
                            statusEl.textContent = 'Error al procesar el archivo. Puedes rellenar los datos manualmente.';
                        }
                    }
                });
            });
        });
    </script>

    {{-- Universal Cookie Consent --}}
    <x-cookie-consent />
</body>
</html>
