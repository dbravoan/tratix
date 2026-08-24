<x-mail::message>
# 🚫 Contrato Cancelado

Te informamos de que el contrato **{{ $contract->reference }}** referente a *{{ $contract->object_description }}* ha sido cancelado por su creador.

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Estado:** Cancelado  
@if($reason)
**Motivo:** {{ $reason }}
@endif
</x-mail::panel>

Los enlaces de revisión o firma asociados a este contrato han quedado desactivados. Si crees que se trata de un error o deseas reiniciar el acuerdo, contacta con la otra parte para generar un nuevo borrador.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
