@php
    $sharing = app(\App\Services\ContractSharing::class);
    $shareLink = $sharing->shareLink($contract);
    $waUrl = $sharing->whatsAppUrl($contract);
    $mailUrl = $sharing->mailToUrl($contract);
    $counterparty = $sharing->counterparty($contract);
    $counterpartyName = $counterparty?->displayName() ?? 'la otra parte';
@endphp

@if($shareLink)
    <div x-data="{
        open: false,
        copied: false,
        link: @js($shareLink),
        copy() {
            navigator.clipboard.writeText(this.link).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
    }">
        {{-- Trigger --}}
        <button type="button" @click="open = true"
            class="inline-flex items-center gap-2 w-full justify-center bg-emerald-950/40 text-emerald-300 border border-emerald-800 hover:bg-emerald-900/50 px-4 py-2 rounded-md text-sm font-medium transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
            </svg>
            Compartir
        </button>

        {{-- Modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-black/60" @click="open = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-slate-800 border border-slate-700 rounded-xl shadow-2xl max-w-md w-full p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-100">Compartir contrato</h3>
                            <p class="text-sm text-slate-400">
                                {{ $contract->reference }} — para que {{ $counterpartyName }} {{ $sharing->actionLabel($contract) }}.
                            </p>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-200 text-2xl leading-none">&times;</button>
                    </div>

                    <div class="space-y-3">
                        {{-- WhatsApp --}}
                        @if($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                               class="flex items-center gap-3 w-full border border-emerald-800 bg-emerald-950/40 hover:bg-emerald-900/60 text-emerald-200 px-4 py-3 rounded-md text-sm font-medium transition">
                                <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.83 14.12c-.25.7-1.45 1.33-2 1.38-.54.05-1.22.24-4.12-.9-3.49-1.37-5.7-4.93-5.87-5.16-.17-.23-1.41-1.87-1.41-3.57s.89-2.53 1.2-2.87c.32-.34.69-.43.92-.43.23 0 .46 0 .66.01.21.01.49-.08.77.59.29.68.97 2.36 1.06 2.53.08.17.14.37.03.6-.11.23-.17.37-.34.57-.17.2-.35.44-.5.59-.17.17-.35.36-.15.71.2.35.89 1.47 1.91 2.38 1.31 1.17 2.42 1.53 2.76 1.7.34.17.54.14.74-.08.2-.23.85-.99 1.08-1.33.23-.34.46-.28.77-.17.31.11 1.98.93 2.32 1.1.34.17.56.25.65.39.08.14.08.82-.17 1.52z"/></svg>
                                <span>{{ $counterparty?->phone ? "WhatsApp de {$counterpartyName}" : 'Enviar por WhatsApp' }}</span>
                            </a>
                        @endif

                        {{-- Email (mailto) --}}
                        @if($mailUrl)
                            <a href="{{ $mailUrl }}"
                               class="flex items-center gap-3 w-full border border-blue-800 bg-blue-950/40 hover:bg-blue-900/60 text-blue-200 px-4 py-3 rounded-md text-sm font-medium transition">
                                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                <span>{{ $counterparty?->email ? "Enviar por email a {$counterpartyName}" : 'Enviar por email' }}</span>
                            </a>
                        @endif

                        {{-- Copy link --}}
                        <button type="button" @click="copy()"
                               class="flex items-center gap-3 w-full border border-slate-700 bg-slate-700/40 hover:bg-slate-700 text-slate-200 px-4 py-3 rounded-md text-sm font-medium transition">
                            <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                            <span x-text="copied ? '✓ Enlace copiado' : 'Copiar enlace'"></span>
                        </button>
                    </div>

                    <div class="mt-4 bg-slate-900 border border-slate-700 rounded-md p-2 text-xs break-all font-mono text-slate-400">{{ $shareLink }}</div>
                </div>
            </div>
        </div>
    </div>
@endif