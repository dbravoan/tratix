<x-mail::message>
# ✏️ Nueva propuesta de modificación

Se ha registrado una propuesta formal de cambio en la redacción del contrato **{{ $contract->reference }}** ({{ $contract->title }}).

**Propuesta por:** {{ $proposal->proposed_by }}  
**Cláusula:** {{ $proposal->clause_title ?? $proposal->clause_key }}

### Texto propuesto:
<x-mail::panel>
{{ $proposal->proposed_text }}
</x-mail::panel>

@if($proposal->reason)
**Motivo:** {{ $proposal->reason }}
@endif

Puedes acceder al contrato para aprobar o rechazar esta modificación:

<x-mail::button :url="$actionUrl">
Revisar propuesta
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
