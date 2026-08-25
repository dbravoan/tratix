@extends('layouts.app')

@section('title', 'Editar contrato – ' . $contract->reference)

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4 py-4" x-data="contractWizard()">
    {{-- Header & Walkthrough Progress Bar --}}
    <div class="mb-6 bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-lg backdrop-blur-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-md bg-blue-500/20 text-blue-300 border border-blue-500/40">
                        Edición de Contrato ({{ $contract->reference }})
                    </span>
                    <span class="text-xs text-slate-400 font-medium" x-text="`Paso ${currentStep} de 5: ${steps[currentStep - 1].name}`"></span>
                </div>
                <h1 class="text-2xl font-bold text-slate-100 mt-1" x-text="steps[currentStep - 1].title"></h1>
                <p class="text-sm text-slate-400 mt-0.5" x-text="steps[currentStep - 1].description"></p>
            </div>

            {{-- Actions and Live Preview Trigger --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('contracts.show', $contract) }}" class="btn-outline px-3.5 py-2 text-xs font-semibold text-slate-300 hover:text-white">
                    ← Volver sin guardar
                </a>
                <button type="button" @click="togglePreview()" class="btn-outline flex items-center gap-2 px-4 py-2 text-xs font-semibold hover:border-emerald-500 hover:text-emerald-300 transition shadow-sm group">
                    <span class="text-base group-hover:scale-110 transition-transform">👁️</span>
                    <span>Ver borrador en vivo</span>
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 border border-slate-700 text-emerald-400" x-text="`${completedFieldsCount}/${totalVariablesCount} datos`"></span>
                </button>
            </div>
        </div>

        {{-- Interactive Step Indicators --}}
        <div class="mt-5">
            <div class="grid grid-cols-5 gap-2 sm:gap-4 text-xs font-medium">
                <template x-for="(step, index) in steps" :key="index">
                    <button type="button" @click="goToStep(index + 1)"
                        class="flex flex-col items-start p-2 rounded-lg transition text-left border"
                        :class="{
                            'bg-emerald-950/60 border-emerald-500 text-emerald-200': currentStep === index + 1,
                            'bg-slate-800/80 border-slate-700 text-slate-300 hover:border-slate-600': currentStep > index + 1,
                            'bg-slate-900/40 border-slate-800 text-slate-500 opacity-80': currentStep < index + 1
                        }">
                        <div class="flex items-center gap-1.5 w-full">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                                :class="{
                                    'bg-emerald-500 text-slate-950': currentStep === index + 1,
                                    'bg-emerald-900 text-emerald-300': currentStep > index + 1,
                                    'bg-slate-800 text-slate-500': currentStep < index + 1
                                }"
                                x-text="currentStep > index + 1 ? '✓' : index + 1"></span>
                            <span class="truncate font-semibold text-[11px] sm:text-xs" x-text="step.shortName"></span>
                        </div>
                    </button>
                </template>
            </div>
            {{-- Progress line --}}
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full transition-all duration-300 ease-out"
                    :style="`width: ${(currentStep / 5) * 100}%`"></div>
            </div>
        </div>
    </div>

    {{-- Main Form Container with Side-by-Side Live Preview Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Main Column: Walkthrough Form --}}
        <div class="lg:col-span-7 xl:col-span-7 space-y-6">
            <form method="POST" action="{{ route('contracts.update', $contract) }}" id="contract-form">
                @csrf
                @method('PUT')

                {{-- PASO 1: Tipo de Contrato y Objeto --}}
                <div x-show="currentStep === 1" x-transition.opacity.duration.250ms class="space-y-6">
                    <section class="bg-slate-800/95 border border-slate-700 rounded-xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                            <h2 class="font-bold text-emerald-400 text-base">1. Datos principales del contrato</h2>
                            <button type="button" @click="fillAllFromTemplate()" class="btn-outline text-xs px-3 py-1.5 flex items-center gap-1.5 text-emerald-300 hover:border-emerald-400 hover:bg-emerald-950/40">
                                <span>✨ Rellenar con datos de ejemplo</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Tipo de contrato *</label>
                                <select name="contract_type" id="contract_type" x-model="form.contract_type" @change="onContractTypeChange()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                                    <option value="arras">Contrato de arras (reserva de inmueble)</option>
                                    <option value="inmuebles">Compraventa de inmueble</option>
                                    <option value="alquiler">Contrato de alquiler / arrendamiento</option>
                                    <option value="vehiculos">Compraventa de vehículo</option>
                                    <option value="bienes_muebles">Compraventa de bienes muebles</option>
                                    <option value="servicios">Prestación de servicios</option>
                                    <option value="prestamo">Préstamo entre particulares</option>
                                    <option value="cesion_derechos">Cesión de derechos</option>
                                    <option value="nda">Acuerdo de confidencialidad (NDA)</option>
                                    <option value="internacional">Compraventa internacional</option>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200">Título del contrato *</label>
                                    <button type="button" @click="fillField('title', 'titlePlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar ejemplo</span>
                                    </button>
                                </div>
                                <input name="title" id="input_title" x-model="form.title" @input="updatePreview()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    :placeholder="currentHints.titlePlaceholder">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.objectTypeLabel"></label>
                                <button type="button" @click="fillField('object_type', 'objectTypePlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                    <span>✨ Usar ejemplo</span>
                                </button>
                            </div>
                            <input name="object_type" id="input_object_type" x-model="form.object_type" @input="updatePreview()"
                                class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                :placeholder="currentHints.objectTypePlaceholder">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.objectDescLabel"></label>
                                <button type="button" @click="fillField('object_description', 'objectDescPlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                    <span>✨ Usar sugerencia</span>
                                </button>
                            </div>
                            <textarea name="object_description" id="input_object_description" x-model="form.object_description" @input="updatePreview()" rows="3"
                                class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                :placeholder="currentHints.objectDescPlaceholder"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Cantidad / Unidades *</label>
                                <input type="number" min="1" name="quantity" id="input_quantity" x-model="form.quantity" @input="updatePreview()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Ciudad de firma *</label>
                                <input name="city" id="input_city" x-model="form.city" @input="updatePreview()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    placeholder="Madrid">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Fecha de firma *</label>
                                <input type="date" name="signing_date" id="input_signing_date" x-model="form.signing_date" @input="updatePreview()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Fecha límite / Efectividad</label>
                                <input type="date" name="effective_date" id="input_effective_date" x-model="form.effective_date" @input="updatePreview()"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="bg-slate-900/80 border border-slate-700/80 rounded-lg p-3.5 mt-2">
                            <label class="block text-xs font-semibold text-slate-200 mb-2">¿Cuál es tu posición en este contrato? *</label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 text-xs font-medium text-slate-200 cursor-pointer">
                                    <input type="radio" name="creator_role" value="vendedor" x-model="form.creator_role" @change="updatePreview()" class="rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                                    <span x-text="`Soy la parte vendedora / transmitente (${currentHints.sellerRoleLabel})`"></span>
                                </label>
                                <label class="flex items-center gap-2 text-xs font-medium text-slate-200 cursor-pointer">
                                    <input type="radio" name="creator_role" value="comprador" x-model="form.creator_role" @change="updatePreview()" class="rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                                    <span x-text="`Soy la parte compradora / adquirente (${currentHints.buyerRoleLabel})`"></span>
                                </label>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- PASO 2: Tus Datos de Identificación (Tu Parte) --}}
                <div x-show="currentStep === 2" x-transition.opacity.duration.250ms class="space-y-6">
                    <section class="bg-slate-800/95 border border-slate-700 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-3 mb-4">
                            <div>
                                <h2 class="font-bold text-emerald-400 text-base" x-text="`2. Tus datos de identificación (${form.creator_role === 'vendedor' ? currentHints.sellerRoleLabel : currentHints.buyerRoleLabel})`"></h2>
                                <p class="text-xs text-slate-400">Modifica tus datos fiscales o escanea un nuevo documento de identidad si es necesario.</p>
                            </div>
                        </div>
                        <div x-show="form.creator_role === 'vendedor'">
                            @include('contracts._party_form', ['role' => 'seller', 'label' => 'Vendedor', 'party' => $seller])
                        </div>
                        <div x-show="form.creator_role === 'comprador'">
                            @include('contracts._party_form', ['role' => 'buyer', 'label' => 'Comprador', 'party' => $buyer])
                        </div>
                    </section>
                </div>

                {{-- PASO 3: Identificación de la Otra Parte (Contraparte) --}}
                <div x-show="currentStep === 3" x-transition.opacity.duration.250ms class="space-y-6">
                    <section class="bg-slate-800/95 border border-slate-700 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-3 mb-4">
                            <div>
                                <h2 class="font-bold text-emerald-400 text-base" x-text="`3. Identificación de la otra parte (${form.creator_role === 'vendedor' ? currentHints.buyerRoleLabel : currentHints.sellerRoleLabel})`"></h2>
                                <p class="text-xs text-slate-400">Modifica los datos de la otra parte o actualiza sus documentos de identidad.</p>
                            </div>
                        </div>
                        <div x-show="form.creator_role === 'vendedor'">
                            @include('contracts._party_form', ['role' => 'buyer', 'label' => 'Comprador', 'party' => $buyer])
                        </div>
                        <div x-show="form.creator_role === 'comprador'">
                            @include('contracts._party_form', ['role' => 'seller', 'label' => 'Vendedor', 'party' => $seller])
                        </div>
                    </section>
                </div>

                {{-- PASO 4: Condiciones Económicas y Cláusulas --}}
                <div x-show="currentStep === 4" x-transition.opacity.duration.250ms class="space-y-6">
                    <section class="bg-slate-800/95 border border-slate-700 rounded-xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                            <h2 class="font-bold text-emerald-400 text-base">4. Condiciones económicas y cláusulas</h2>
                            <button type="button" @click="fillAllFromTemplate()" class="btn-outline text-xs px-3 py-1.5 flex items-center gap-1.5 text-emerald-300 hover:border-emerald-400 hover:bg-emerald-950/40">
                                <span>✨ Autocompletar condiciones de ejemplo</span>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.priceLabel"></label>
                                    <button type="button" @click="fillField('price_amount', 'pricePlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar ejemplo</span>
                                    </button>
                                </div>
                                <input type="number" step="0.01" min="0.01" name="price_amount" id="price_amount" x-model="form.price_amount" @input="updateTotal(); updatePreview();"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" :placeholder="currentHints.pricePlaceholder">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Impuestos estimados (IVA / ITP)</label>
                                <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" x-model="form.tax_amount" @input="updateTotal(); updatePreview();"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-200 mb-1">Divisa</label>
                                <input name="currency" id="currency" x-model="form.currency" @input="updateTotal(); updatePreview();" maxlength="3"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-700/80 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-300">Total de la operación económica:</span>
                            <span id="total_preview" class="text-base font-bold text-emerald-400">0,00 EUR</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.paymentLabel"></label>
                                    <button type="button" @click="fillField('payment_terms', 'paymentPlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar sugerencia</span>
                                    </button>
                                </div>
                                <textarea name="payment_terms" id="input_payment_terms" x-model="form.payment_terms" @input="updatePreview()" rows="2"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    :placeholder="currentHints.paymentPlaceholder"></textarea>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.deliveryLabel"></label>
                                    <button type="button" @click="fillField('delivery_terms', 'deliveryPlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar sugerencia</span>
                                    </button>
                                </div>
                                <textarea name="delivery_terms" id="input_delivery_terms" x-model="form.delivery_terms" @input="updatePreview()" rows="2"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    :placeholder="currentHints.deliveryPlaceholder"></textarea>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.warrantiesLabel"></label>
                                    <button type="button" @click="fillField('warranties', 'warrantiesPlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar sugerencia</span>
                                    </button>
                                </div>
                                <textarea name="warranties" id="input_warranties" x-model="form.warranties" @input="updatePreview()" rows="2"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    :placeholder="currentHints.warrantiesPlaceholder"></textarea>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-200" x-text="currentHints.specialLabel"></label>
                                    <button type="button" @click="fillField('special_clauses', 'specialPlaceholder')" class="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1 font-medium transition">
                                        <span>✨ Usar sugerencia</span>
                                    </button>
                                </div>
                                <textarea name="special_clauses" id="input_special_clauses" x-model="form.special_clauses" @input="updatePreview()" rows="2"
                                    class="w-full border border-slate-600 bg-slate-900 text-slate-100 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 placeholder-slate-500"
                                    :placeholder="currentHints.specialPlaceholder"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="bg-emerald-950/40 border border-emerald-800 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-base">⚖️</span>
                            <h2 class="font-bold text-emerald-400 text-sm">Régimen legal detectado automáticamente</h2>
                        </div>
                        <p class="text-xs text-slate-200" id="regime_preview">Calculando según tipo de parte y país...</p>
                        <ul class="text-[11px] text-slate-300 mt-2 space-y-1" id="regime_notes"></ul>
                    </section>
                </div>

                {{-- PASO 5: Revisión y Guardar Cambios --}}
                <div x-show="currentStep === 5" x-transition.opacity.duration.250ms class="space-y-6">
                    <section class="bg-slate-800/95 border border-slate-700 rounded-xl p-5 shadow-sm space-y-4">
                        <h2 class="font-bold text-emerald-400 text-base border-b border-slate-700 pb-2">5. Resumen y Guardado de Modificaciones</h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="bg-slate-900/80 p-3.5 rounded-lg border border-slate-700">
                                <span class="text-slate-400 font-medium block">Contrato y Objeto</span>
                                <span class="font-bold text-slate-100 text-sm block mt-0.5" x-text="form.title || 'Sin título'"></span>
                                <span class="text-slate-300 block mt-1" x-text="`Tipo: ${form.contract_type} · Ciudad: ${form.city || 'No indicada'}`"></span>
                                <span class="text-slate-400 block mt-1" x-text="`Objeto: ${form.object_type || '-'}`"></span>
                            </div>

                            <div class="bg-slate-900/80 p-3.5 rounded-lg border border-slate-700">
                                <span class="text-slate-400 font-medium block">Aspectos Económicos</span>
                                <span class="font-bold text-emerald-400 text-sm block mt-0.5" x-text="`${form.price_amount || '0'} ${form.currency}`"></span>
                                <span class="text-slate-300 block mt-1" x-text="`Pago: ${form.payment_terms || 'Estándar'}`"></span>
                            </div>

                            <div class="bg-slate-900/80 p-3.5 rounded-lg border border-slate-700">
                                <span class="text-slate-400 font-medium block">Parte Vendedora</span>
                                <span class="font-bold text-slate-100 block mt-0.5" x-text="getPartyName('seller')"></span>
                                <span class="text-slate-300 block" x-text="`NIF/CIF: ${getPartyTaxId('seller')}`"></span>
                                <span class="text-slate-400 block" x-text="`Domicilio: ${getPartyAddress('seller')}`"></span>
                            </div>

                            <div class="bg-slate-900/80 p-3.5 rounded-lg border border-slate-700">
                                <span class="text-slate-400 font-medium block">Parte Compradora</span>
                                <span class="font-bold text-slate-100 block mt-0.5" x-text="getPartyName('buyer')"></span>
                                <span class="text-slate-300 block" x-text="`NIF/CIF: ${getPartyTaxId('buyer')}`"></span>
                                <span class="text-slate-400 block" x-text="`Domicilio: ${getPartyAddress('buyer')}`"></span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-blue-950/40 border border-blue-800 rounded-lg text-xs text-blue-300 flex items-start gap-2.5">
                            <span class="text-lg shrink-0">📝</span>
                            <div>
                                <p class="font-semibold">Actualización del documento legal</p>
                                <p class="text-slate-300 text-[11px] mt-0.5">Al guardar los cambios se regenerarán las cláusulas correspondientes manteniendo la trazabilidad y las evidencias de auditoría.</p>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Wizard Navigation Controls --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <button type="button" x-show="currentStep > 1" @click="prevStep()" class="btn-outline px-5 py-2.5 text-xs font-semibold flex items-center gap-1.5">
                        <span>← Paso anterior</span>
                    </button>
                    <div x-show="currentStep === 1"></div>

                    <div class="flex items-center gap-3">
                        <button type="button" x-show="currentStep < 5" @click="nextStep()" class="btn-primary px-6 py-2.5 text-xs font-semibold flex items-center gap-1.5 shadow-md">
                            <span>Continuar al siguiente paso →</span>
                        </button>
                        <button type="submit" x-show="currentStep === 5" class="btn-primary px-8 py-3 text-sm font-bold flex items-center gap-2 shadow-lg hover:shadow-emerald-500/20">
                            <span>💾 Guardar Cambios en el Contrato</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Right Column: Dynamic Live Contract Base Preview --}}
        <div class="lg:col-span-5 xl:col-span-5 sticky top-6 space-y-4" x-show="showPreviewOnDesktop || isPreviewModalOpen" :class="{'fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md p-4 flex items-center justify-center': isPreviewModalOpen}">
            <div class="bg-slate-900 border border-slate-700/90 rounded-2xl p-5 shadow-2xl flex flex-col h-[750px] max-h-[85vh] w-full" :class="{'max-w-3xl': isPreviewModalOpen}">
                {{-- Preview Header --}}
                <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-950 border border-emerald-800 flex items-center justify-center text-emerald-400 text-xs font-bold">
                            📜
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                                Borrador del Contrato en Vivo
                            </h3>
                            <p class="text-[11px] text-slate-400">Actualizado dinámicamente con los cambios realizados.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="isPreviewModalOpen" @click="isPreviewModalOpen = false" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white text-xs">✕ Cerrar</button>
                    </div>
                </div>

                {{-- Preview Body --}}
                <div class="overflow-y-auto pr-2 space-y-4 font-serif text-xs leading-relaxed text-slate-300 bg-slate-950/70 p-4 rounded-xl border border-slate-800/80 select-text" id="live_contract_preview">
                    {{-- Title --}}
                    <div class="text-center pb-2 border-b border-slate-800">
                        <h4 class="font-sans font-extrabold text-sm uppercase tracking-wide text-slate-100" id="preview_title">
                            {{ $contract->title }}
                        </h4>
                        <p class="text-[11px] font-sans text-slate-400 mt-1">
                            En <span class="preview-var text-emerald-400 font-semibold" id="preview_city">{{ $contract->city }}</span>, a <span class="preview-var text-emerald-400 font-semibold" id="preview_signing_date">{{ $contract->signing_date?->format('d/m/Y') }}</span>
                        </p>
                    </div>

                    {{-- Intervinientes --}}
                    <div>
                        <h5 class="font-sans font-bold text-emerald-400 text-xs uppercase mb-1">REUNIDOS</h5>
                        <p class="mb-2">
                            <strong>DE UNA PARTE</strong>, como PARTE VENDEDORA:
                            <span class="preview-var text-emerald-400 font-semibold" id="preview_seller_name">{{ $seller?->displayName() }}</span>,
                            con documento fiscal / NIF <span class="preview-var text-emerald-400 font-semibold" id="preview_seller_tax_id">{{ $seller?->tax_id }}</span>,
                            y domicilio en <span class="preview-var text-emerald-400 font-semibold" id="preview_seller_address">{{ $seller?->address }}</span>.
                        </p>
                        <p>
                            <strong>DE OTRA PARTE</strong>, como PARTE COMPRADORA:
                            <span class="preview-var text-emerald-400 font-semibold" id="preview_buyer_name">{{ $buyer?->displayName() }}</span>,
                            con documento fiscal / NIF <span class="preview-var text-emerald-400 font-semibold" id="preview_buyer_tax_id">{{ $buyer?->tax_id }}</span>,
                            y domicilio en <span class="preview-var text-emerald-400 font-semibold" id="preview_buyer_address">{{ $buyer?->address }}</span>.
                        </p>
                    </div>

                    {{-- Exponen --}}
                    <div>
                        <h5 class="font-sans font-bold text-emerald-400 text-xs uppercase mb-1">EXPONEN</h5>
                        <p>
                            Que la PARTE VENDEDORA es legítima titular del bien / derecho consistente en:
                            <span class="preview-var text-emerald-400 font-semibold" id="preview_object_description">{{ $contract->object_description }}</span>.
                            Estando ambas partes interesadas en formalizar la operación, convienen las siguientes:
                        </p>
                    </div>

                    {{-- Cláusulas --}}
                    <div>
                        <h5 class="font-sans font-bold text-emerald-400 text-xs uppercase mb-1">CLÁUSULAS</h5>
                        
                        <p class="mb-2">
                            <strong>PRIMERA. Objeto.</strong> La parte vendedora transmite a la parte compradora el bien descrito (<span class="preview-var text-emerald-400 font-semibold" id="preview_object_type">{{ $contract->object_type ?? $contract->title }}</span>), que adquiere en las condiciones pactadas.
                        </p>

                        <p class="mb-2">
                            <strong>SEGUNDA. Precio y condiciones de pago.</strong> El precio total de la presente operación se fija en la cantidad de <span class="preview-var text-emerald-400 font-semibold font-sans font-bold" id="preview_price">{{ number_format($contract->price_amount, 2, ',', '.') }} {{ $contract->currency }}</span>.
                            <span class="preview-var text-emerald-400" id="preview_payment_terms">{{ $contract->payment_terms }}</span>
                        </p>

                        <div class="mb-2 p-2.5 rounded bg-emerald-950/30 border border-emerald-800/60" id="preview_specific_clause">
                            <strong class="text-emerald-300 font-sans block mb-1" id="preview_specific_title">TERCERA. Cláusula Específica</strong>
                            <span class="text-slate-300" id="preview_specific_body">Cláusulas adaptadas conforme a la legislación civil y mercantil aplicable.</span>
                        </div>

                        <p class="mb-2">
                            <strong>CUARTA. Plazo de formalización y entrega.</strong> <span class="preview-var text-emerald-400" id="preview_delivery_terms">{{ $contract->delivery_terms ?? 'La entrega se formalizará en el plazo pactado.' }}</span>
                        </p>

                        <p class="mb-2">
                            <strong>QUINTA. Estado de cargas y garantías.</strong> <span class="preview-var text-emerald-400" id="preview_warranties">{{ $contract->warranties ?? 'El bien se transmite libre de cargas no declaradas.' }}</span>
                        </p>

                        {{-- Cláusula 6: Derechos y obligaciones --}}
                        <div class="mb-2.5 p-3 rounded-xl bg-slate-900/70 border border-slate-800 space-y-2">
                            <strong class="text-emerald-300 font-sans block text-xs">SEXTA. Derechos y obligaciones recíprocas de las partes</strong>
                            <div class="space-y-1.5 text-[11px]">
                                <div>
                                    <span class="font-bold text-emerald-400 block text-[10px] uppercase">1. PARTE VENDEDORA:</span>
                                    <ul class="space-y-0.5 pl-3 list-none text-slate-300">
                                        <li class="flex items-start gap-1.5"><span class="text-emerald-400">•</span><span>Derecho al cobro íntegro del precio convenido en la forma y plazos estipulados.</span></li>
                                        <li class="flex items-start gap-1.5"><span class="text-emerald-400">•</span><span>Obligación de entrega del bien libre de cargas y saneamiento por vicios ocultos.</span></li>
                                    </ul>
                                </div>
                                <div>
                                    <span class="font-bold text-emerald-400 block text-[10px] uppercase">2. PARTE COMPRADORA:</span>
                                    <ul class="space-y-0.5 pl-3 list-none text-slate-300">
                                        <li class="flex items-start gap-1.5"><span class="text-emerald-400">•</span><span>Derecho a la recepción del bien en el estado declarado y titularidad pacífica.</span></li>
                                        <li class="flex items-start gap-1.5"><span class="text-emerald-400">•</span><span>Obligación de abono del precio pactado y liquidación de impuestos o tasas locales.</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Cláusula 7: Fuero --}}
                        <p>
                            <strong>SÉPTIMA. Ley aplicable y jurisdicción.</strong> Las partes se someten a la legislación aplicable y a los juzgados y tribunales competentes.
                        </p>
                    </div>

                    {{-- Firmas --}}
                    <div class="pt-3 border-t border-slate-800 grid grid-cols-2 gap-4 text-center font-sans text-[11px]">
                        <div class="p-2 border border-dashed border-slate-700 rounded bg-slate-900/60">
                            <span class="text-slate-400 block mb-1">Por la Parte Vendedora</span>
                            <span class="font-bold text-slate-200" id="preview_signature_seller">{{ $seller?->displayName() }}</span>
                        </div>
                        <div class="p-2 border border-dashed border-slate-700 rounded bg-slate-900/60">
                            <span class="text-slate-400 block mb-1">Por la Parte Compradora</span>
                            <span class="font-bold text-slate-200" id="preview_signature_buyer">{{ $buyer?->displayName() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<script>
    function contractWizard() {
        return {
            currentStep: 1,
            showPreviewOnDesktop: true,
            isPreviewModalOpen: false,
            completedFieldsCount: 10,
            totalVariablesCount: 10,
            form: {
                contract_type: @json(old('contract_type', $contract->contract_type)),
                title: @json(old('title', $contract->title)),
                object_type: @json(old('object_type', $contract->object_type)),
                object_description: @json(old('object_description', $contract->object_description)),
                quantity: '{{ old('quantity', $contract->quantity ?? 1) }}',
                city: @json(old('city', $contract->city)),
                signing_date: @json(old('signing_date', $contract->signing_date?->format('Y-m-d'))),
                effective_date: @json(old('effective_date', $contract->effective_date?->format('Y-m-d'))),
                creator_role: @json(old('creator_role', $contract->creator_role ?? 'vendedor')),
                price_amount: '{{ old('price_amount', $contract->price_amount) }}',
                tax_amount: '{{ old('tax_amount', $contract->tax_amount ?? 0) }}',
                currency: @json(old('currency', $contract->currency ?? 'EUR')),
                payment_terms: @json(old('payment_terms', $contract->payment_terms ?? '')),
                delivery_terms: @json(old('delivery_terms', $contract->delivery_terms ?? '')),
                warranties: @json(old('warranties', $contract->warranties ?? '')),
                special_clauses: @json(old('special_clauses', $contract->special_clauses ?? '')),
            },
            typeHints: {
                arras: {
                    titlePlaceholder: 'p. ej. Contrato de arras penitenciales para vivienda sita en C/ Mayor 15',
                    objectTypeLabel: 'Inmueble objeto de reserva *',
                    objectTypePlaceholder: 'p. ej. Vivienda sita en C/ Mayor 15, 3ºB, Madrid',
                    objectDescLabel: 'Descripción registral, catastral y física del inmueble *',
                    objectDescPlaceholder: 'Referencia catastral: 9872023VK..., Finca registral nº 12345 en el Registro de la Propiedad nº 3 de Madrid, superficie de 95 m², plaza de garaje nº 4 y trastero nº 12. Estado: libre de ocupantes.',
                    priceLabel: 'Precio total acordado de compraventa *',
                    pricePlaceholder: 'Ej. 180000',
                    paymentLabel: 'Importe de las arras / señal y forma de abono',
                    paymentPlaceholder: 'p. ej. Se entrega la cantidad de 18.000 € en concepto de arras penitenciales (art. 1454 CC) mediante transferencia bancaria. El resto (162.000 €) se abonará en el acto de elevación a escritura pública.',
                    deliveryLabel: 'Plazo límite de otorgamiento de escritura pública notarial',
                    deliveryPlaceholder: 'p. ej. La escritura pública de compraventa se otorgará antes del 31/12/2026 ante el Notario que libremente designe la parte compradora.',
                    warrantiesLabel: 'Estado de cargas, hipotecas y comunidad',
                    warrantiesPlaceholder: 'p. ej. El inmueble se transmitirá libre de hipotecas, embargos y arrendatarios, y al corriente de gastos ordinarios de comunidad e IBI.',
                    specialLabel: 'Pactos especiales (condición hipotecaria, mobiliario)',
                    specialPlaceholder: 'p. ej. Condición resolutoria por denegación de préstamo hipotecario, inventario de electrodomésticos de cocina incluido.',
                    sellerRoleLabel: 'Vendedora / Propietaria',
                    buyerRoleLabel: 'Compradora / Reservante'
                },
                vehiculos: {
                    titlePlaceholder: 'p. ej. Contrato de compraventa de vehículo usado',
                    objectTypeLabel: 'Vehículo (Marca, Modelo y Matrícula) *',
                    objectTypePlaceholder: 'p. ej. Turismo Seat Ibiza 1.6 TDI Style, Matrícula 1234-XYZ',
                    objectDescLabel: 'Datos técnicos del vehículo (Bastidor VIN, km, ITV) *',
                    objectDescPlaceholder: 'Número de bastidor (VIN): VSSZZZ6JZ..., Kilometraje actual: 120.000 km, Color: Blanco, Primera matriculación: 15/03/2019, ITV en vigor hasta: 04/2027.',
                    priceLabel: 'Precio total del vehículo *',
                    pricePlaceholder: 'Ej. 7500',
                    paymentLabel: 'Forma y plazo de pago del precio',
                    paymentPlaceholder: 'p. ej. Pago íntegro de 7.500 € mediante transferencia bancaria inmediata en el momento de la entrega del vehículo, llaves y documentación.',
                    deliveryLabel: 'Entrega del vehículo y cambio de titularidad DGT',
                    deliveryPlaceholder: 'p. ej. La entrega de llaves y documentación se formaliza en el acto de la firma. El comprador tramitará el cambio de titularidad en la DGT en un plazo máximo de 15 días.',
                    warrantiesLabel: 'Garantía mecánica, estado y cargas del vehículo',
                    warrantiesPlaceholder: 'p. ej. El vendedor declara que el vehículo no tiene reservas de dominio, precintos ni multas pendientes, y que el kilometraje es real. Entrega informe oficial DGT.',
                    specialLabel: 'Equipamiento adicional o acuerdos sobre revisiones',
                    specialPlaceholder: 'p. ej. Se incluye juego de 4 neumáticos de repuesto y libro oficial de mantenimiento al día con última revisión en taller oficial.',
                    sellerRoleLabel: 'Vendedora / Titular del Vehículo',
                    buyerRoleLabel: 'Compradora / Adquirente'
                },
                inmuebles: {
                    titlePlaceholder: 'p. ej. Contrato de compraventa de inmueble sito en C/ Gran Vía 12',
                    objectTypeLabel: 'Inmueble / Finca a transmitir *',
                    objectTypePlaceholder: 'p. ej. Vivienda / Local sito en C/ Gran Vía 12, 2ºA, Madrid',
                    objectDescLabel: 'Descripción registral y catastral completa *',
                    objectDescPlaceholder: 'Referencia catastral: 1234567AB1234C, Finca registral nº 56789, Tomo 1500, Libro 400, superficie útil 110 m², linderos y anejos inseparables.',
                    priceLabel: 'Precio total del inmueble *',
                    pricePlaceholder: 'Ej. 250000',
                    paymentLabel: 'Forma y condiciones de pago',
                    paymentPlaceholder: 'p. ej. Abono mediante cheque bancario conformado nominativo en el acto de otorgamiento de la escritura pública ante Notario.',
                    deliveryLabel: 'Fecha y Notaría para otorgamiento de escritura pública',
                    deliveryPlaceholder: 'p. ej. Otorgamiento de escritura pública el día 15/10/2026 ante Notario con entrega de llaves y toma de posesión.',
                    warrantiesLabel: 'Cargas, hipotecas, IBI y comunidad',
                    warrantiesPlaceholder: 'p. ej. Libre de arrendatarios, hipotecas y gravámenes. El vendedor aportará certificado de estar al corriente de la comunidad de propietarios y último IBI.',
                    specialLabel: 'Distribución de gastos y pactos adicionales',
                    specialPlaceholder: 'p. ej. Distribución de gastos conforme a ley (Notaría, Registro e ITP por comprador; Plusvalía municipal por vendedor).',
                    sellerRoleLabel: 'Vendedora / Transmitente',
                    buyerRoleLabel: 'Compradora / Adquirente'
                },
                alquiler: {
                    titlePlaceholder: 'p. ej. Contrato de arrendamiento de vivienda sita en C/ Serrano 10',
                    objectTypeLabel: 'Inmueble / Vivienda arrendada *',
                    objectTypePlaceholder: 'p. ej. Vivienda amueblada en C/ Serrano 10, 4º Dcha, Madrid',
                    objectDescLabel: 'Descripción de la vivienda y equipamiento incluido *',
                    objectDescPlaceholder: 'Vivienda de 85 m², 2 dormitorios, salón, cocina equipada con electrodomésticos y baño. Se adjunta inventario fotográfico de mobiliario en el anexo.',
                    priceLabel: 'Renta mensual acordada *',
                    pricePlaceholder: 'Ej. 1200',
                    paymentLabel: 'Fianza legal, garantía y abono de la renta mensual',
                    paymentPlaceholder: 'p. ej. Renta de 1.200 €/mes abonable dentro de los primeros 5 días de cada mes por transferencia. Se entrega 1 mes de fianza legal (1.200 €) y 1 mes de garantía adicional.',
                    deliveryLabel: 'Duración del contrato y entrega de llaves',
                    deliveryPlaceholder: 'p. ej. Duración inicial de 1 año prorrogable hasta 5 años conforme a la LAU. Entrega de llaves y posesión el día 01/09/2026.',
                    warrantiesLabel: 'Gastos, suministros y mantenimiento',
                    warrantiesPlaceholder: 'p. ej. Suministros individualizados (luz, agua, gas) a cargo del arrendatario. Gastos de comunidad e IBI a cargo del arrendador.',
                    specialLabel: 'Normas de uso y cláusulas especiales',
                    specialPlaceholder: 'p. ej. Destino exclusivo a vivienda habitual. Prohibición expresa de subarriendo, cesión o alquiler turístico.',
                    sellerRoleLabel: 'Arrendadora / Propietaria',
                    buyerRoleLabel: 'Arrendataria / Inquilina'
                },
                servicios: {
                    titlePlaceholder: 'p. ej. Contrato de prestación de servicios de desarrollo de software',
                    objectTypeLabel: 'Servicio profesional a prestar *',
                    objectTypePlaceholder: 'p. ej. Desarrollo de plataforma web y aplicación móvil a medida',
                    objectDescLabel: 'Alcance detallado de los servicios y entregables *',
                    objectDescPlaceholder: 'Alcance del proyecto: arquitectura backend en Laravel, diseño UI/UX, desarrollo de API REST, pasarela de pago, testing y despliegue en producción con soporte de 3 meses.',
                    priceLabel: 'Honorarios / Precio total del servicio *',
                    pricePlaceholder: 'Ej. 6000',
                    paymentLabel: 'Hitos de pago y facturación',
                    paymentPlaceholder: 'p. ej. 30% a la firma del contrato, 40% a la entrega de la versión funcional beta y 30% a la puesta en producción y entrega final. Facturas a 30 días.',
                    deliveryLabel: 'Cronograma de entregas y finalización',
                    deliveryPlaceholder: 'p. ej. Plazo total de ejecución de 90 días naturales a partir de la firma, con reuniones quincenales de seguimiento y sprints ágiles.',
                    warrantiesLabel: 'Nivel de servicio (SLA) y cesión de propiedad intelectual',
                    warrantiesPlaceholder: 'p. ej. Todos los derechos de propiedad intelectual del software desarrollado se ceden en exclusiva al cliente una vez abonado el precio total.',
                    specialLabel: 'Confidencialidad y no competencia',
                    specialPlaceholder: 'p. ej. Cláusula de confidencialidad y no captación de personal durante la vigencia del contrato y 1 año posterior.',
                    sellerRoleLabel: 'Prestadora del Servicio / Proveedora',
                    buyerRoleLabel: 'Cliente / Contratante'
                },
                prestamo: {
                    titlePlaceholder: 'p. ej. Contrato de préstamo personal de dinero entre particulares',
                    objectTypeLabel: 'Capital / Importe prestado *',
                    objectTypePlaceholder: 'p. ej. Préstamo dinerario de 10.000 € para financiación personal',
                    objectDescLabel: 'Finalidad y cuenta bancaria de abono *',
                    objectDescPlaceholder: 'Préstamo concedido por el prestamista al prestatario para [finalidad], transferido a la cuenta bancaria ES12 3456... de la que el prestatario es titular.',
                    priceLabel: 'Importe total del capital prestado *',
                    pricePlaceholder: 'Ej. 10000',
                    paymentLabel: 'Plazo de amortización, cuotas e intereses',
                    paymentPlaceholder: 'p. ej. Préstamo sin devengo de intereses (tipo 0%). Devolución en 24 cuotas mensuales de 416,66 € mediante transferencia bancaria los días 1 a 5 de cada mes.',
                    deliveryLabel: 'Fecha de entrega de fondos y vencimiento final',
                    deliveryPlaceholder: 'p. ej. Transferencia inmediata de fondos el día de la firma. Vencimiento final de la última cuota el 31/08/2028.',
                    warrantiesLabel: 'Garantías de devolución y vencimiento anticipado',
                    warrantiesPlaceholder: 'p. ej. En caso de impago de dos cuotas consecutivas, el prestamista podrá resolver el contrato y exigir la devolución íntegra inmediata del saldo pendiente.',
                    specialLabel: 'Amortización anticipada o pactos especiales',
                    specialPlaceholder: 'p. ej. El prestatario podrá amortizar total o parcialmente el préstamo en cualquier momento sin penalización ni comisión alguna.',
                    sellerRoleLabel: 'Prestamista / Acreedora',
                    buyerRoleLabel: 'Prestataria / Deudora'
                },
                bienes_muebles: {
                    titlePlaceholder: 'p. ej. Contrato de compraventa de maquinaria industrial',
                    objectTypeLabel: 'Bien mueble / Maquinaria / Equipamiento *',
                    objectTypePlaceholder: 'p. ej. Torno industrial CNC modelo XYZ-2000',
                    objectDescLabel: 'Especificaciones técnicas, número de serie y accesorios *',
                    objectDescPlaceholder: 'Número de serie: SN-987654, fabricante, año de fabricación: 2022, potencia 5kW, accesorios incluidos y estado de conservación operativo.',
                    priceLabel: 'Precio total de los bienes *',
                    pricePlaceholder: 'Ej. 12500',
                    paymentLabel: 'Forma de pago y reserva de dominio',
                    paymentPlaceholder: 'p. ej. 50% a la formalización del pedido y 50% tras la entrega y puesta en marcha en las instalaciones del comprador.',
                    deliveryLabel: 'Lugar y plazo de entrega / transporte',
                    deliveryPlaceholder: 'p. ej. Entrega en el almacén del comprador antes del 15/10/2026. Gastos de transporte y seguro a cargo de la parte vendedora.',
                    warrantiesLabel: 'Garantía de funcionamiento y vicios ocultos',
                    warrantiesPlaceholder: 'p. ej. Garantía de 1 año contra defectos de fabricación o vicios ocultos, incluyendo mano de obra y sustitución de piezas defectuosas.',
                    specialLabel: 'Instalación, puesta en marcha y formación',
                    specialPlaceholder: 'p. ej. El precio incluye 8 horas de formación técnica presencial para los operarios del comprador.',
                    sellerRoleLabel: 'Vendedora / Proveedora',
                    buyerRoleLabel: 'Compradora / Adquirente'
                },
                nda: {
                    titlePlaceholder: 'p. ej. Acuerdo de confidencialidad y no divulgación (NDA)',
                    objectTypeLabel: 'Proyecto o información confidencial protegida *',
                    objectTypePlaceholder: 'p. ej. Información y negociaciones sobre el proyecto tecnológico [Nombre]',
                    objectDescLabel: 'Alcance de la información confidencial y finalidad *',
                    objectDescPlaceholder: 'Toda información técnica, de código fuente, financiera, comercial o de clientes intercambiada con motivo del análisis de una posible operación conjunta.',
                    priceLabel: 'Indemnización pactada por incumplimiento (o 0 si sin canon)',
                    pricePlaceholder: 'Ej. 0',
                    paymentLabel: 'Tratamiento y uso de la información confidencial',
                    paymentPlaceholder: 'p. ej. Uso exclusivo para la finalidad acordada. Prohibición estricta de copia, cesión o ingeniería inversa sin consentimiento previo por escrito.',
                    deliveryLabel: 'Periodo de vigencia de la confidencialidad',
                    deliveryPlaceholder: 'p. ej. La obligación de confidencialidad subsistirá durante un plazo de 3 años a contar desde la firma del presente acuerdo.',
                    warrantiesLabel: 'Devolución o destrucción de información',
                    warrantiesPlaceholder: 'p. ej. A requerimiento de cualquiera de las partes, la otra devolverá o certificará la destrucción de toda la información confidencial recibida.',
                    specialLabel: 'Cláusulas adicionales (no captación de personal)',
                    specialPlaceholder: 'p. ej. Prohibición de captación de directivos o empleados (non-solicitation) durante un periodo de 2 años.',
                    sellerRoleLabel: 'Parte Emisora / Divulgadora',
                    buyerRoleLabel: 'Parte Receptora'
                },
                cesion_derechos: {
                    titlePlaceholder: 'p. ej. Contrato de cesión de derechos de propiedad intelectual / marca',
                    objectTypeLabel: 'Derecho / Marca / Obra transferida *',
                    objectTypePlaceholder: 'p. ej. Marca registrada nº 123456 en la OEPM o software desarrollado',
                    objectDescLabel: 'Alcance territorial, modalidades y registros *',
                    objectDescPlaceholder: 'Número de registro en la Oficina de Patentes y Marcas (OEPM/EUIPO), clases amparadas, ámbito geográfico mundial y modalidades de explotación cedidas.',
                    priceLabel: 'Precio o canon de cesión *',
                    pricePlaceholder: 'Ej. 5000',
                    paymentLabel: 'Forma y calendario de pago',
                    paymentPlaceholder: 'p. ej. Abono único a la firma mediante transferencia, o liquidación de royalties semestrales.',
                    deliveryLabel: 'Efectividad e inscripción en registros oficiales',
                    deliveryPlaceholder: 'p. ej. Efectividad inmediata desde la firma y compromiso de firmar las instancias necesarias para la inscripción registral.',
                    warrantiesLabel: 'Garantía de titularidad y pacífica posesión',
                    warrantiesPlaceholder: 'p. ej. El cedente garantiza que es el titular legítimo y exclusivo de los derechos y que están libres de gravámenes o litigios de terceros.',
                    specialLabel: 'Pactos de no agresión y asistencia técnica',
                    specialPlaceholder: 'p. ej. El cedente facilitará toda la documentación necesaria para la explotación del derecho cedido.',
                    sellerRoleLabel: 'Parte Cedente',
                    buyerRoleLabel: 'Parte Cesionaria'
                },
                internacional: {
                    titlePlaceholder: 'p. ej. Contrato de compraventa internacional de mercancías (CISG)',
                    objectTypeLabel: 'Mercancía / Goods *',
                    objectTypePlaceholder: 'p. ej. Lote de 500 unidades de componentes electrónicos',
                    objectDescLabel: 'Especificaciones técnicas, Incoterms y aduanas *',
                    objectDescPlaceholder: 'Incoterms 2020 acordados (ej. CIF Puerto de Valencia), especificaciones técnicas, embalaje marítimo y código arancelario HS.',
                    priceLabel: 'Importe total de la compraventa *',
                    pricePlaceholder: 'Ej. 45000',
                    paymentLabel: 'Condiciones de pago internacional',
                    paymentPlaceholder: 'p. ej. Crédito documentario irrevocable y confirmado (Carta de Crédito) pagadero a la vista contra presentación de documentos de embarque.',
                    deliveryLabel: 'Plazo de embarque y puerto de destino',
                    deliveryPlaceholder: 'p. ej. Embarque antes del 30/11/2026 con entrega en el Puerto de destino convenido.',
                    warrantiesLabel: 'Inspección de mercancías y reclamación de vicios',
                    warrantiesPlaceholder: 'p. ej. Aplicación del Convenio de Viena de 1980 (CISG) y plazo de 15 días tras recepción para notificación de disconformidades.',
                    specialLabel: 'Cláusula de fuerza mayor e idioma oficial',
                    specialPlaceholder: 'p. ej. Cláusula de fuerza mayor según ICC y sumisión a arbitraje de la Corte de Arbitraje de Madrid.',
                    sellerRoleLabel: 'Parte Vendedora / Exportadora',
                    buyerRoleLabel: 'Parte Compradora / Importadora'
                }
            },
            get currentHints() {
                return this.typeHints[this.form.contract_type] || this.typeHints['arras'];
            },
            get steps() {
                const isSeller = this.form.creator_role === 'vendedor';
                return [
                    { shortName: '1. Objeto & Rol', name: 'Tipo y Objeto', title: '1. Modifica el tipo de contrato, tu posición y el objeto', description: 'Revisa qué vas a transmitir, la posición en el contrato, la ciudad de firma y las fechas.' },
                    { shortName: '2. Tus Datos', name: 'Tus Datos', title: '2. Tus datos de identificación (' + (isSeller ? this.currentHints.sellerRoleLabel : this.currentHints.buyerRoleLabel) + ')', description: 'Modifica tus datos fiscales o escanea un nuevo documento de identidad si procede.' },
                    { shortName: '3. Contraparte', name: 'La Otra Parte', title: '3. Identificación de la otra parte (' + (isSeller ? this.currentHints.buyerRoleLabel : this.currentHints.sellerRoleLabel) + ')', description: 'Modifica los datos de la otra parte o actualiza sus documentos de identidad.' },
                    { shortName: '4. Condiciones', name: 'Condiciones y Pago', title: '4. Condiciones económicas y cláusulas', description: 'Modifica el precio, señal, plazos de otorgamiento y garantías.' },
                    { shortName: '5. Revisar', name: 'Guardar Cambios', title: '5. Resumen final y confirmación', description: 'Comprueba los cambios realizados antes de guardarlos.' },
                ];
            },
            init() {
                this.$nextTick(() => {
                    this.updatePreview();
                    this.updateTotal();
                    computeRegime();

                    document.querySelectorAll('input, select, textarea').forEach(input => {
                        input.addEventListener('input', () => {
                            this.updatePreview();
                            this.updateTotal();
                        });
                        input.addEventListener('change', () => {
                            this.updatePreview();
                            this.updateTotal();
                            computeRegime();
                        });
                    });
                });
            },
            goToStep(stepNumber) {
                if (stepNumber >= 1 && stepNumber <= 5) {
                    this.currentStep = stepNumber;
                    this.updatePreview();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            nextStep() {
                if (this.currentStep < 5) {
                    this.currentStep++;
                    this.updatePreview();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.updatePreview();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            togglePreview() {
                this.isPreviewModalOpen = !this.isPreviewModalOpen;
            },
            cleanPlaceholder(text) {
                if (!text) return '';
                return text.replace(/^p\.\s*ej\.\s*/i, '').replace(/^Ej\.\s*/i, '').trim();
            },
            fillField(fieldName, hintKey) {
                const rawVal = this.currentHints[hintKey] || '';
                const cleanVal = this.cleanPlaceholder(rawVal);
                this.form[fieldName] = cleanVal;
                
                const el = document.getElementById('input_' + fieldName) || document.getElementById(fieldName);
                if (el) {
                    el.value = cleanVal;
                }
                
                this.updatePreview();
                this.updateTotal();
            },
            fillAllFromTemplate() {
                this.fillField('title', 'titlePlaceholder');
                this.fillField('object_type', 'objectTypePlaceholder');
                this.fillField('object_description', 'objectDescPlaceholder');
                this.fillField('price_amount', 'pricePlaceholder');
                this.fillField('payment_terms', 'paymentPlaceholder');
                this.fillField('delivery_terms', 'deliveryPlaceholder');
                this.fillField('warranties', 'warrantiesPlaceholder');
                this.fillField('special_clauses', 'specialPlaceholder');
                this.updatePreview();
                this.updateTotal();
                computeRegime();
            },
            onContractTypeChange() {
                const currentTitle = (this.form.title || '').trim();
                const isGenericOrEmpty = !currentTitle || Object.values(this.typeHints).some(h => this.cleanPlaceholder(h.titlePlaceholder).toLowerCase() === currentTitle.toLowerCase());
                if (isGenericOrEmpty) {
                    this.form.title = this.cleanPlaceholder(this.currentHints.titlePlaceholder);
                    const titleEl = document.getElementById('input_title');
                    if (titleEl) titleEl.value = this.form.title;
                }
                this.updatePreview();
                computeRegime();
            },
            getPartyName(role) {
                const partyTypeEl = document.querySelector(`[name="${role}[party_type]"]`) || document.getElementById(`${role}_party_type`);
                const isPart = !partyTypeEl || partyTypeEl.value === 'particular';
                const fullInput = document.querySelector(`[name="${role}[full_name]"]`) || document.getElementById(`${role}_input_full_name`);
                const compInput = document.querySelector(`[name="${role}[company_name]"]`) || document.getElementById(`${role}_input_company_name`);

                const fullName = fullInput?.value?.trim() || '';
                const compName = compInput?.value?.trim() || '';

                const val = isPart ? (fullName || compName) : (compName || fullName);
                return val || `[${role === 'seller' ? 'PARTE VENDEDORA' : 'PARTE COMPRADORA'}]`;
            },
            getPartyTaxId(role) {
                const taxInput = document.querySelector(`[name="${role}[tax_id]"]`) || document.getElementById(`${role}_tax_id`);
                const countryInput = document.querySelector(`[name="${role}[tax_id_country]"]`) || document.getElementById(`${role}_tax_id_country`);
                const taxId = taxInput?.value?.trim();
                const country = countryInput?.value?.trim() || 'ES';
                return taxId ? `${taxId} (${country})` : `[NIF/CIF ${role === 'seller' ? 'VENDEDOR' : 'COMPRADOR'}]`;
            },
            getPartyAddress(role) {
                const addrInput = document.querySelector(`[name="${role}[address]"]`) || document.getElementById(`${role}_address`);
                const cpInput = document.querySelector(`[name="${role}[postal_code]"]`) || document.getElementById(`${role}_postal_code`);
                const cityInput = document.querySelector(`[name="${role}[city]"]`) || document.getElementById(`${role}_city`);
                const countryInput = document.querySelector(`[name="${role}[country]"]`) || document.getElementById(`${role}_country`);

                const addr = addrInput?.value?.trim() || '';
                const cp = cpInput?.value?.trim() || '';
                const city = cityInput?.value?.trim() || '';
                const country = countryInput?.value?.trim() || '';

                const parts = [addr, cp, city, country].filter(Boolean);
                return parts.length > 0 ? parts.join(', ') : `[DOMICILIO ${role === 'seller' ? 'VENDEDOR' : 'COMPRADOR'}]`;
            },
            updatePreview() {
                const el = (id, text, isDefault = false) => {
                    const node = document.getElementById(id);
                    if (node) {
                        node.textContent = text;
                        node.className = isDefault ? 'preview-var text-amber-300 font-mono text-[11px] bg-amber-950/50 px-1 rounded' : 'preview-var text-emerald-400 font-semibold';
                    }
                };

                let filled = 0;
                const check = (val) => { if (val && String(val).trim()) filled++; };

                el('preview_title', this.form.title || 'CONTRATO', !this.form.title);
                el('preview_city', this.form.city || '[CIUDAD]', !this.form.city);
                el('preview_signing_date', this.form.signing_date || '[FECHA]', !this.form.signing_date);
                check(this.form.title);
                check(this.form.city);

                const sellerName = this.getPartyName('seller');
                const sellerTax = this.getPartyTaxId('seller');
                const sellerAddr = this.getPartyAddress('seller');
                el('preview_seller_name', sellerName, sellerName.includes('['));
                el('preview_seller_tax_id', sellerTax, sellerTax.includes('['));
                el('preview_seller_address', sellerAddr, sellerAddr.includes('['));
                el('preview_signature_seller', sellerName, sellerName.includes('['));
                check(sellerName.includes('[') ? '' : sellerName);
                check(sellerTax.includes('[') ? '' : sellerTax);

                const buyerName = this.getPartyName('buyer');
                const buyerTax = this.getPartyTaxId('buyer');
                const buyerAddr = this.getPartyAddress('buyer');
                el('preview_buyer_name', buyerName, buyerName.includes('['));
                el('preview_buyer_tax_id', buyerTax, buyerTax.includes('['));
                el('preview_buyer_address', buyerAddr, buyerAddr.includes('['));
                el('preview_signature_buyer', buyerName, buyerName.includes('['));
                check(buyerName.includes('[') ? '' : buyerName);
                check(buyerTax.includes('[') ? '' : buyerTax);

                el('preview_object_type', this.form.object_type || '[OBJETO]', !this.form.object_type);
                el('preview_object_description', this.form.object_description || this.form.object_type || '[DESCRIPCIÓN]', !this.form.object_description);
                check(this.form.object_description);

                const priceFormatted = (parseFloat(this.form.price_amount) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2 }) + ' ' + (this.form.currency || 'EUR');
                el('preview_price', this.form.price_amount ? priceFormatted : '[PRECIO]', !this.form.price_amount);
                check(this.form.price_amount);

                el('preview_payment_terms', this.form.payment_terms ? `Condiciones de abono: ${this.form.payment_terms}` : 'El precio se abonará conforme a los plazos acordados.', !this.form.payment_terms);
                el('preview_delivery_terms', this.form.delivery_terms ? `La entrega o formalización se efectuará conforme a: ${this.form.delivery_terms}` : 'La entrega o formalización se efectuará en el plazo pactado.', !this.form.delivery_terms);
                el('preview_warranties', this.form.warranties || 'El bien se transmite libre de cargas no declaradas.', !this.form.warranties);
                check(this.form.payment_terms);

                const typeClauses = {
                    'arras': {
                        title: 'TERCERA. Arras Penitenciales (Art. 1454 Código Civil)',
                        body: 'La cantidad entregada tiene carácter de ARRAS PENITENCIALES. Si la parte compradora desiste perderá la señal; si desiste la parte vendedora deberá devolverlas duplicadas.'
                    },
                    'inmuebles': {
                        title: 'TERCERA. Escritura Pública y Libre de Cargas',
                        body: 'El inmueble se transmitirá libre de hipotecas, embargos y arrendatarios en la fecha pactada ante Notario.'
                    },
                    'alquiler': {
                        title: 'TERCERA. Condiciones de Arrendamiento (LAU)',
                        body: 'La vivienda se destina a uso habitual del arrendatario con depósito de fianza y cumplimiento de la Ley de Arrendamientos Urbanos.'
                    },
                    'vehiculos': {
                        title: 'TERCERA. Estado del Vehículo y Transferencia DGT',
                        body: 'El vendedor responderá del saneamiento por vicios ocultos y se formalizará el cambio de titularidad en la Dirección General de Tráfico.'
                    },
                    'nda': {
                        title: 'TERCERA. Confidencialidad y Secreto Profesional',
                        body: 'Las partes se obligan a no divulgar ni hacer uso no autorizado de la información técnica, comercial o financiera recibida.'
                    },
                    'prestamo': {
                        title: 'TERCERA. Obligación de Devolución del Capital',
                        body: 'El prestatario se obliga a reembolsar el importe íntegro recibido en los plazos y términos acordados.'
                    },
                };

                const currentTypeClause = typeClauses[this.form.contract_type] || {
                    title: 'TERCERA. Cláusulas Especiales de la Operación',
                    body: 'Las partes cumplirán con las obligaciones de entrega, saneamiento y pago conforme al Código Civil y legislación aplicable.'
                };

                const specTitle = document.getElementById('preview_specific_title');
                const specBody = document.getElementById('preview_specific_body');
                if (specTitle) specTitle.textContent = currentTypeClause.title;
                if (specBody) specBody.textContent = currentTypeClause.body;

                this.completedFieldsCount = filled;
            },
            updateTotal() {
                const price = parseFloat(this.form.price_amount) || 0;
                const tax = parseFloat(this.form.tax_amount) || 0;
                const currency = this.form.currency || 'EUR';
                const el = document.getElementById('total_preview');
                if (el) {
                    el.textContent = (price + tax).toLocaleString('es-ES', { minimumFractionDigits: 2 }) + ' ' + currency;
                }
            }
        };
    }

    const regimeMap = {
        b2b: { label: 'B2B – entre profesionales', badge: 'bg-blue-950/60 text-blue-300 border border-blue-800' },
        b2c: { label: 'B2C – venta a consumidor', badge: 'bg-emerald-950/60 text-emerald-300 border border-emerald-800' },
        c2c: { label: 'C2C – entre particulares', badge: 'bg-amber-950/60 text-amber-300 border border-amber-800' },
        c2b: { label: 'C2B – compra por profesional', badge: 'bg-rose-950/60 text-rose-300 border border-rose-800' },
    };

    function computeRegime() {
        const sellerType = document.querySelector('[name="seller[party_type]"]')?.value || 'particular';
        const buyerType = document.querySelector('[name="buyer[party_type]"]')?.value || 'particular';
        const sellerCountry = (document.querySelector('[name="seller[country]"]')?.value || 'ES').toUpperCase();
        const buyerCountry = (document.querySelector('[name="buyer[country]"]')?.value || 'ES').toUpperCase();
        const euCountries = ['AT','BE','BG','CY','CZ','DE','DK','EE','EL','ES','FI','FR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK'];

        const prof = t => t === 'autonomo' || t === 'sociedad';
        const sP = prof(sellerType), bP = prof(buyerType);
        let txn = sP && bP ? 'b2b' : sP && !bP ? 'b2c' : !sP && !bP ? 'c2c' : 'c2b';
        let jur = sellerCountry === buyerCountry ? 'nacional' : (euCountries.includes(sellerCountry) && euCountries.includes(buyerCountry) ? 'intracomunitario' : 'internacional');

        const el = document.getElementById('regime_preview');
        if (el) {
            el.innerHTML = '<span class="font-bold uppercase text-emerald-400">' + txn + '</span> · ' + jur
                + (txn === 'b2b' && jur === 'intracomunitario' ? ' · <span class="font-bold text-emerald-300">Inversión del sujeto pasivo</span>' : '');
        }

        const notes = document.getElementById('regime_notes');
        if (notes) {
            notes.innerHTML = '';
            const notesByRegime = {
                'b2b-intracomunitario': 'IVA 0% en origen; el comprador declara el IVA con inversión del sujeto pasivo (art. 84 Ley 37/1992). Ambos necesitan número de IVA válido en VIES.',
                'b2b-nacional': 'IVA repercutido por el vendedor (Ley 37/1992); factura conforme RD 1619/2012.',
                'b2c': 'IVA incluido en el precio; garantía legal y derecho de desistimiento (RDL 1/2007).',
                'c2c': 'Sin IVA; sujeta a ITP (RDL 1/1993) que abona el adquirente.',
                'c2b': 'Sin IVA; posible retención IRPF 3% (art. 100 RIRPF).',
            };
            const key = txn + '-' + jur;
            if (notesByRegime[key]) {
                const li = document.createElement('li');
                li.textContent = '• ' + notesByRegime[key];
                notes.appendChild(li);
            }
        }
    }

    document.querySelectorAll('[name="seller[party_type]"], [name="buyer[party_type]"], [name="seller[country]"], [name="buyer[country]"]')
        .forEach(el => el.addEventListener('change', computeRegime));

    document.querySelectorAll('[name="seller[party_type]"], [name="buyer[party_type]"]')
        .forEach(el => el.addEventListener('change', () => togglePartyFields(el.name)));

    function togglePartyFields(name) {
        const role = name.split('[')[0];
        const type = document.querySelector(`[name="${role}[party_type]"]`)?.value;
        const isParticular = type === 'particular';
        const fullNameBlock = document.getElementById(`${role}_full_name`);
        const companyBlock = document.getElementById(`${role}_company_name`);
        if (fullNameBlock) fullNameBlock.style.display = isParticular ? '' : 'none';
        if (companyBlock) companyBlock.style.display = isParticular ? 'none' : '';
    }

    document.querySelectorAll('[name="seller[party_type]"], [name="buyer[party_type]"]')
        .forEach(el => togglePartyFields(el.name));

    async function checkTaxId(role) {
        const country = document.querySelector(`[name="${role}[tax_id_country]"]`).value;
        const taxId = document.querySelector(`[name="${role}[tax_id]"]`).value;
        const statusEl = document.getElementById(`${role}_tax_status`);
        if (!taxId) return;
        statusEl.textContent = 'Verificando…';

        try {
            const res = await fetch(`{{ route('contracts.tax-id-check') }}?country=${encodeURIComponent(country)}&tax_id=${encodeURIComponent(taxId)}`);
            const data = await res.json();
            if (!data.valid) {
                statusEl.textContent = '✗ Formato no válido.';
                statusEl.className = 'text-xs mt-1 text-rose-400 font-medium';
            } else {
                let msg = '✓ Formato válido (' + (data.type || 'desconocido') + ')';
                if (data.vies_checked !== undefined) {
                    msg += data.vies_checked
                        ? (data.vies_valid ? ' · VIES: válido' : ' · VIES: NO válido')
                        : ' · VIES: no disponible';
                }
                statusEl.textContent = msg;
                statusEl.className = 'text-xs mt-1 ' + (data.vies_checked && !data.vies_valid ? 'text-rose-400 font-medium' : 'text-emerald-400 font-medium');
            }
        } catch (e) {
            statusEl.textContent = 'Error comprobando.';
            statusEl.className = 'text-xs mt-1 text-slate-500';
        }
    }

    document.querySelectorAll('.tax-check').forEach(btn => {
        btn.addEventListener('click', () => checkTaxId(btn.dataset.role));
    });

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

                        // Contrast and grayscale normalization to reduce reflections
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
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
            inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            pill.remove();
        });

        document.getElementById(`btn_dismiss_${inputEl.id}`)?.addEventListener('click', () => {
            pill.remove();
        });

        return { autoFilled: false, suggested: true };
    }

    // Identity card OCR scanning handler (Anverso / Reverso / Cámara)
    document.querySelectorAll('.js-id-scanner').forEach(input => {
        input.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const role = input.dataset.role;
            const side = input.dataset.side || 'front';
            const sideLabel = side === 'front' ? 'Anverso' : 'Reverso';
            const statusEl = document.getElementById(`${role}_scan_status`);
            const badgeEl = document.getElementById(`${role}_${side}_badge`);
            const slotEl = document.getElementById(`${role}_slot_${side}`);
            const previewEl = document.getElementById(`${role}_preview_${side}`);
            const thumbEl = document.getElementById(`${role}_thumb_${side}`);
            const nameEl = document.getElementById(`${role}_name_${side}`);

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

            statusEl.classList.remove('hidden', 'bg-rose-950/60', 'text-rose-300', 'border-rose-800', 'bg-emerald-950/60', 'text-emerald-300', 'border-emerald-800');
            statusEl.classList.add('bg-amber-950/60', 'text-amber-300', 'border-amber-800');
            statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">🔍</span> Analizando <strong>${sideLabel}</strong> (${file.name}) con OCR...`;

            // Run high-contrast client OCR with live progress
            let ocrText = null;
            try {
                ocrText = await preprocessAndOcrImage(file, (pct) => {
                    statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">🔍</span> Leyendo texto de <strong>${sideLabel}</strong>: ${pct}%...`;
                });
            } catch (ocrErr) {
                console.log('Client OCR error, falling back to server', ocrErr);
            }

            statusEl.innerHTML = `<span class="inline-block animate-spin mr-1.5">⚡</span> Validando datos fiscales y extrayendo campos de <strong>${sideLabel}</strong>...`;

            const formData = new FormData();
            formData.append('document', file);
            formData.append('side', side);
            if (ocrText) {
                formData.append('ocr_text', ocrText);
            }
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('contracts.scan-id') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al procesar el archivo');
                }

                const data = await response.json();

                // Store tokens
                if (data.scan_token) {
                    if (side === 'front') {
                        const frontToken = document.getElementById(`${role}_id_card_front_token`);
                        if (frontToken) frontToken.value = data.scan_token;
                    } else {
                        const backToken = document.getElementById(`${role}_id_card_back_token`);
                        if (backToken) backToken.value = data.scan_token;
                    }
                    const tokenEl = document.getElementById(`${role}_id_card_token`);
                    if (tokenEl && !tokenEl.value) tokenEl.value = data.scan_token;
                }

                // Update UI slot
                if (badgeEl) {
                    badgeEl.className = 'text-[10px] text-emerald-400 font-semibold';
                    badgeEl.textContent = '✓ ' + sideLabel + ' cargado';
                }
                if (slotEl) {
                    slotEl.classList.remove('border-dashed', 'border-slate-700');
                    slotEl.classList.add('border-solid', 'border-emerald-500/80', 'bg-emerald-950/20');
                }

                let suggestionsCount = 0;

                // Smart fill or suggest
                if (data.party_type) {
                    const partyTypeSelect = document.getElementById(`${role}_party_type`);
                    if (partyTypeSelect && partyTypeSelect.value !== data.party_type) {
                        partyTypeSelect.value = data.party_type;
                        togglePartyFields(`${role}[party_type]`);
                    }
                }

                if (data.full_name) {
                    const nameInput = document.getElementById(`${role}_input_full_name`);
                    const res = smartFillOrSuggest(nameInput, data.full_name, 'Nombre');
                    if (res?.suggested) suggestionsCount++;
                }

                if (data.tax_id) {
                    const taxIdInput = document.getElementById(`${role}_tax_id`);
                    const res = smartFillOrSuggest(taxIdInput, data.tax_id, 'NIF/NIE');
                    if (res?.suggested) suggestionsCount++;
                }

                if (data.tax_id_country) {
                    const taxCountryInput = document.getElementById(`${role}_tax_id_country`);
                    if (taxCountryInput && !taxCountryInput.value) taxCountryInput.value = data.tax_id_country;
                    const countryInput = document.getElementById(`${role}_country`);
                    if (countryInput && !countryInput.value) countryInput.value = data.tax_id_country;
                }

                if (data.address) {
                    const addressInput = document.getElementById(`${role}_address`);
                    const res = smartFillOrSuggest(addressInput, data.address, 'Dirección');
                    if (res?.suggested) suggestionsCount++;
                }

                if (data.postal_code) {
                    const postalInput = document.getElementById(`${role}_postal_code`);
                    const res = smartFillOrSuggest(postalInput, data.postal_code, 'Código Postal');
                    if (res?.suggested) suggestionsCount++;
                }

                if (data.city) {
                    const cityInput = document.getElementById(`${role}_city`);
                    const res = smartFillOrSuggest(cityInput, data.city, 'Ciudad');
                    if (res?.suggested) suggestionsCount++;
                }

                if (data.province) {
                    const provInput = document.getElementById(`${role}_province`);
                    const res = smartFillOrSuggest(provInput, data.province, 'Provincia');
                    if (res?.suggested) suggestionsCount++;
                }

                computeRegime();
                if (data.tax_id) {
                    checkTaxId(role);
                }

                // Trigger live preview update
                const wizardEl = document.querySelector('[x-data="contractWizard()"]');
                if (wizardEl && wizardEl.__x) {
                    wizardEl.__x.$data.updatePreview();
                }

                statusEl.classList.remove('bg-amber-950/60', 'text-amber-300', 'border-amber-800');
                statusEl.classList.add('bg-emerald-950/60', 'text-emerald-300', 'border-emerald-800');
                
                const identifiedInfo = [data.full_name, data.tax_id, data.city].filter(Boolean).join(' · ');
                let successMsg = `<strong>✓ ${sideLabel} procesado con éxito:</strong> ${identifiedInfo || 'Datos extraídos'}. Archivo vinculado legalmente al contrato.`;
                if (suggestionsCount > 0) {
                    successMsg += ` <span class="text-amber-300 ml-1">(${suggestionsCount} sugerencia(s) disponible(s) sin sobreescribir tus datos).</span>`;
                }
                statusEl.innerHTML = successMsg;
            } catch (err) {
                statusEl.classList.remove('bg-amber-950/60', 'text-amber-300', 'border-amber-800');
                statusEl.classList.add('bg-rose-950/60', 'text-rose-300', 'border-rose-800');
                statusEl.textContent = `✗ No se pudo procesar el ${sideLabel}. Puedes rellenar los datos manualmente si lo prefieres.`;
            }
        });
    });

    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', () => {
            const wizardEl = document.querySelector('[x-data="contractWizard()"]');
            if (wizardEl && wizardEl.__x) {
                wizardEl.__x.$data.updatePreview();
            }
        });
    });
</script>
@endsection
