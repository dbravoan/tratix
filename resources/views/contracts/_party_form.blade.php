@php
    $party = $party ?? null;
    $creatorRoleVal = old('creator_role', $contract->creator_role ?? 'vendedor');
    $isCreatorParty = ($party === null && auth()->check() && ($creatorRoleVal === ($role === 'seller' ? 'vendedor' : 'comprador')));
    $defaults = $isCreatorParty ? auth()->user()->defaultPartyData() : [];
    $partyType = old("{$role}.party_type", $party?->party_type ?? ($defaults['party_type'] ?? 'particular'));
    $isParticular = $partyType === 'particular';
@endphp

{{-- Dual-Slot ID Card Scanner (Anverso + Reverso + Cámara) --}}
<div class="mb-5 bg-slate-900/90 border border-slate-700/90 rounded-xl p-4 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-950/70 border border-emerald-800 flex items-center justify-center text-emerald-400 font-bold shrink-0 text-sm">
                🪪
            </span>
            <div>
                <h4 class="text-sm font-semibold text-slate-100">Documento de Identidad (DNI / NIE / Pasaporte)</h4>
                <p class="text-xs text-slate-400">Sube o fotografía el <strong>Anverso</strong> y <strong>Reverso</strong> para auto-completar los datos y adjuntarlos automáticamente con validez legal.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- Slot 1: Anverso (Front) --}}
        <div class="border border-dashed border-slate-700 rounded-lg p-3 bg-slate-950/50 hover:border-emerald-500/60 transition flex flex-col justify-between" id="{{ $role }}_slot_front">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-slate-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                        1. Anverso (Nombre, Foto, DNI)
                    </span>
                    <span id="{{ $role }}_front_badge" class="text-[10px] text-slate-500 font-medium">Pendiente</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-2">Cara delantera con foto, nombre y número de documento.</p>
                
                {{-- Live Photo Preview --}}
                <div id="{{ $role }}_preview_front" class="hidden mb-2.5 p-2 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2.5">
                    <img id="{{ $role }}_thumb_front" src="" alt="Anverso DNI" class="h-12 w-16 object-cover rounded border border-slate-700 shrink-0">
                    <div class="flex-1 min-w-0">
                        <span class="text-[11px] font-semibold text-emerald-400 block truncate" id="{{ $role }}_name_front">anverso.jpg</span>
                        <span class="text-[10px] text-slate-400">✓ Listo para adjuntar</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-emerald-500 hover:text-emerald-300">
                    <span>📷 Hacer foto</span>
                    <input type="file" class="hidden js-id-scanner" data-role="{{ $role }}" data-side="front" accept="image/*" capture="environment">
                </label>
                <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-slate-500 text-slate-300">
                    <span>📁 Subir archivo</span>
                    <input type="file" class="hidden js-id-scanner" data-role="{{ $role }}" data-side="front" accept="image/*,.pdf">
                </label>
            </div>
        </div>

        {{-- Slot 2: Reverso (Back) --}}
        <div class="border border-dashed border-slate-700 rounded-lg p-3 bg-slate-950/50 hover:border-emerald-500/60 transition flex flex-col justify-between" id="{{ $role }}_slot_back">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-slate-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                        2. Reverso (Dirección, Código MRZ)
                    </span>
                    <span id="{{ $role }}_back_badge" class="text-[10px] text-slate-500 font-medium">Pendiente</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-2">Cara trasera con domicilio y líneas de lectura mecánica.</p>
                
                {{-- Live Photo Preview --}}
                <div id="{{ $role }}_preview_back" class="hidden mb-2.5 p-2 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2.5">
                    <img id="{{ $role }}_thumb_back" src="" alt="Reverso DNI" class="h-12 w-16 object-cover rounded border border-slate-700 shrink-0">
                    <div class="flex-1 min-w-0">
                        <span class="text-[11px] font-semibold text-blue-400 block truncate" id="{{ $role }}_name_back">reverso.jpg</span>
                        <span class="text-[10px] text-slate-400">✓ Listo para adjuntar</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-blue-500 hover:text-blue-300">
                    <span>📷 Hacer foto</span>
                    <input type="file" class="hidden js-id-scanner" data-role="{{ $role }}" data-side="back" accept="image/*" capture="environment">
                </label>
                <label class="btn-outline cursor-pointer text-[11px] py-1.5 px-2.5 flex items-center gap-1.5 hover:border-slate-500 text-slate-300">
                    <span>📁 Subir archivo</span>
                    <input type="file" class="hidden js-id-scanner" data-role="{{ $role }}" data-side="back" accept="image/*,.pdf">
                </label>
            </div>
        </div>
    </div>

    <div id="{{ $role }}_scan_status" class="hidden mt-3 text-xs p-2.5 rounded-lg border transition" aria-live="polite"></div>
    <input type="hidden" name="{{ $role }}[id_card_front_token]" id="{{ $role }}_id_card_front_token" value="{{ old("{$role}.id_card_front_token") }}">
    <input type="hidden" name="{{ $role }}[id_card_back_token]" id="{{ $role }}_id_card_back_token" value="{{ old("{$role}.id_card_back_token") }}">
    <input type="hidden" name="{{ $role }}[id_card_token]" id="{{ $role }}_id_card_token" value="{{ old("{$role}.id_card_token") }}">
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Tipo de parte *</label>
        <select name="{{ $role }}[party_type]" id="{{ $role }}_party_type" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            <option value="particular" @selected($isParticular)>Particular</option>
            <option value="autonomo" @selected($partyType === 'autonomo')>Autónomo / Empresario individual</option>
            <option value="sociedad" @selected($partyType === 'sociedad')>Sociedad / Entidad jurídica</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">País (residencia fiscal) *</label>
        <input name="{{ $role }}[country]" id="{{ $role }}_country" value="{{ old("{$role}.country", $party?->country ?? ($defaults['country'] ?? 'ES')) }}" maxlength="2" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="ES">
    </div>
    <div id="{{ $role }}_full_name" style="{{ $isParticular ? '' : 'display:none' }}">
        <label class="block text-sm font-medium text-slate-200 mb-1">Nombre y apellidos *</label>
        <input name="{{ $role }}[full_name]" id="{{ $role }}_input_full_name" value="{{ old("{$role}.full_name", $party?->full_name ?? ($defaults['full_name'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500">
    </div>
    <div id="{{ $role }}_company_name" style="{{ $isParticular ? 'display:none' : '' }}">
        <label class="block text-sm font-medium text-slate-200 mb-1">Razón social *</label>
        <input name="{{ $role }}[company_name]" id="{{ $role }}_input_company_name" value="{{ old("{$role}.company_name", $party?->company_name ?? ($defaults['company_name'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">NIF / CIF / NIE / N.º IVA *</label>
        <div class="flex gap-2">
            <input name="{{ $role }}[tax_id_country]" id="{{ $role }}_tax_id_country" value="{{ old("{$role}.tax_id_country", $party?->tax_id_country ?? ($defaults['tax_id_country'] ?? 'ES')) }}" maxlength="2" class="w-14 border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-2 py-2 text-sm uppercase focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            <input name="{{ $role }}[tax_id]" id="{{ $role }}_tax_id" value="{{ old("{$role}.tax_id", $party?->tax_id ?? ($defaults['tax_id'] ?? '')) }}" class="flex-1 border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="p. ej. 12345678Z / B12345678 / FR…">
            <button type="button" data-role="{{ $role }}" class="tax-check bg-slate-700 hover:bg-slate-600 text-slate-200 border border-slate-600 px-3 rounded-md text-sm font-medium transition">Verificar</button>
        </div>
        <p id="{{ $role }}_tax_status" class="text-xs mt-1 text-slate-400" aria-live="polite"></p>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">EORI (opcional)</label>
        <input name="{{ $role }}[eori]" id="{{ $role }}_eori" value="{{ old("{$role}.eori", $party?->eori) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="EORI (tráfico extracomunitario)">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Dirección *</label>
        <input name="{{ $role }}[address]" id="{{ $role }}_address" value="{{ old("{$role}.address", $party?->address ?? ($defaults['address'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="Calle, número, piso...">
    </div>
    <div class="grid grid-cols-3 gap-2">
        <div>
            <label class="block text-sm font-medium text-slate-200 mb-1">C.P. *</label>
            <input name="{{ $role }}[postal_code]" id="{{ $role }}_postal_code" value="{{ old("{$role}.postal_code", $party?->postal_code ?? ($defaults['postal_code'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="28001">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-200 mb-1">Ciudad *</label>
            <input name="{{ $role }}[city]" id="{{ $role }}_city" value="{{ old("{$role}.city", $party?->city ?? ($defaults['city'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="Madrid">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Provincia / región</label>
        <input name="{{ $role }}[province]" id="{{ $role }}_province" value="{{ old("{$role}.province", $party?->province) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Email</label>
        <input type="email" name="{{ $role }}[email]" id="{{ $role }}_email" value="{{ old("{$role}.email", $party?->email ?? ($defaults['email'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Teléfono (WhatsApp)</label>
        <input name="{{ $role }}[phone]" id="{{ $role }}_phone" value="{{ old("{$role}.phone", $party?->phone ?? ($defaults['phone'] ?? '')) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="+34 600 000 000">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-200 mb-1">Actividad profesional</label>
        <input name="{{ $role }}[activity]" id="{{ $role }}_activity" value="{{ old("{$role}.activity", $party?->activity) }}" class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500" placeholder="solo si es profesional">
    </div>
    <div class="md:col-span-2 flex items-center gap-6 pt-1">
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="{{ $role }}[registered_vat]" value="1" @checked(old("{$role}.registered_vat", $party?->registered_vat)) class="rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500"> Inscrito en VIES / operador intracomunitario (ROI)
        </label>
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="{{ $role }}[acting_in_own_name]" value="1" @checked(old("{$role}.acting_in_own_name", $party?->acting_in_own_name ?? true)) class="rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500"> Actúa en nombre propio
        </label>
    </div>

    <div class="md:col-span-2 pt-2">
        <x-gdpr-info-box title="Tratamiento de datos personales de {{ $role === 'seller' ? 'la Parte Vendedora' : 'la Parte Compradora' }} (RGPD)" />
    </div>
</div>
