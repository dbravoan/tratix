<x-mail::message>
# 🔐 Código de Verificación de Firma

Introduce el siguiente código de seguridad para verificar tu correo electrónico y confirmar tu firma en el contrato **{{ $contract->reference }}**:

<x-mail::panel>
<div style="text-align: center; padding: 12px 0;">
<span style="font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #10b981; font-family: monospace;">{{ $code }}</span>
</div>
</x-mail::panel>

* ⏱️ Este código es válido durante **10 minutos**.
* 🛡️ Es de un solo uso y personal.
* ⚠️ Si no has solicitado firmar este documento, ignora este correo.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
