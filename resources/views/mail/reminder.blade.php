<x-mail::message>
# ⏰ Recordatorio de Contrato

{{ $reminderMessage }}

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Título:** {{ $contract->title }}  
**Estado actual:** {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
</x-mail::panel>

<x-mail::button :url="$actionUrl">
Abrir contrato y gestionar
</x-mail::button>

*Si ya completaste la acción requerida, puedes ignorar este recordatorio.*

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
