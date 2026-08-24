<x-mail::message>
# 📊 Resumen Mensual de Actividad

Hola **{{ $user->name }}**,

Aquí tienes el balance mensual de tus contratos y firmas en **{{ config('app.name') }}**:

<x-mail::panel>
* 🛡️ **{{ $signedThisMonth }}** contrato(s) firmado(s) y sellados eIDAS con éxito este mes.
* ⏳ **{{ $pending }}** contrato(s) en curso (pendientes de revisión o firma).
* 📑 **{{ $totalContracts }}** contrato(s) gestionados en total.
</x-mail::panel>

Accede a tu panel para consultar tus acuerdos, descargar certificados de evidencia o redactar nuevos contratos con validez jurídica:

<x-mail::button :url="route('dashboard')">
Acceder a mi panel de contratos
</x-mail::button>

Gracias por confiar en {{ config('app.name') }} para la redacción y firma de tus acuerdos legales.

Un cordial saludo,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
