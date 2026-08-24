<x-mail::message>
# 🔍 Invitación para Revisar Contrato

Hola,

Han compartido contigo el borrador del contrato **{{ $contract->reference }}** (*{{ $contract->object_description }}*) para que lo revises como **{{ ucfirst($recipientRole) }}**.

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Rol asignado:** {{ ucfirst($recipientRole) }}  
**Objeto:** {{ $contract->object_description }}
</x-mail::panel>

### ¿Qué puedes hacer en la plataforma?
* 📖 **Leer el documento íntegro** y consultar tus derechos y obligaciones legales.
* 💬 **Dejar comentarios y dudas** en cualquier cláusula.
* ✏️ **Proponer cambios en la redacción** con control de versiones.
* ✅ **Aceptar el borrador** cuando estés conforme para proceder a la firma.

<x-mail::button :url="$reviewUrl">
Acceder y revisar contrato
</x-mail::button>

*El enlace es personal y seguro. No necesitas registrarte para revisar el contrato.*

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
