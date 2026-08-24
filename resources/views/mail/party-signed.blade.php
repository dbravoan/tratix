<x-mail::message>
# ✍️ Firma registrada en el contrato

**{{ $signerName }}** ha completado su firma electrónica como **{{ ucfirst($signerRole) }}** en el contrato **{{ $contract->reference }}** (*{{ $contract->object_description }}*).

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Firmante:** {{ $signerName }} ({{ ucfirst($signerRole) }})  
**Fecha y hora:** {{ now()->format('d/m/Y H:i') }} UTC
</x-mail::panel>

@if($isPendingParty)
**¡Es tu turno!** Ya puedes acceder con tu enlace seguro para revisar el documento y estampar tu firma electrónica para cerrar y sellar el contrato legalmente.

<x-mail::button :url="$actionUrl">
Firmar contrato ahora
</x-mail::button>
@else
Puedes seguir el progreso de las firmas y consultar el contrato en el panel de control:

<x-mail::button :url="$actionUrl">
Ver estado de las firmas
</x-mail::button>
@endif

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
