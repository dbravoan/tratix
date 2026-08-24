<x-mail::message>
# ✍️ Invitación para Firmar Contrato

Hola,

El contrato **{{ $contract->reference }}** referente a *{{ $contract->object_description }}* ha sido aprobado y está listo para su **firma electrónica**.

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Título:** {{ $contract->title }}  
**Estado:** Listo para Firma
</x-mail::panel>

Antes de firmar podrás consultar el documento íntegro y el desglose de tus derechos y obligaciones. Puedes estampar tu firma de forma segura desde tu teléfono móvil o desde tu ordenador.

<x-mail::button :url="$signUrl">
Firmar contrato ahora
</x-mail::button>

*Firma electrónica simple con verificación de identidad (eIDAS) y sello de tiempo de integridad.*

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
