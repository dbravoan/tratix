<x-mail::message>
# ✅ Borrador aceptado

La contraparte **{{ $acceptorName }}** ({{ ucfirst($acceptorRole) }}) ha revisado y **aceptado el borrador** del contrato **{{ $contract->reference }}** ({{ $contract->title }}).

El contrato está listo para que congeles la versión definitiva y procedas al envío para firma digital.

<x-mail::button :url="$actionUrl">
Pasar contrato a firma
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
