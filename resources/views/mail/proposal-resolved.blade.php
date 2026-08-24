<x-mail::message>
# 📋 Propuesta de cláusula {{ $proposal->status === 'approved' ? 'aprobada ✓' : 'rechazada ✗' }}

La propuesta de modificación en la cláusula **{{ $proposal->clause_title ?? $proposal->clause_key }}** para el contrato **{{ $contract->reference }}** ha sido **{{ $proposal->status === 'approved' ? 'aprobada y aplicada al documento' : 'rechazada' }}**.

### Texto {{ $proposal->status === 'approved' ? 'aprobado' : 'propuesto' }}:
<x-mail::panel>
{{ $proposal->proposed_text }}
</x-mail::panel>

Puedes consultar el estado actual del borrador en el siguiente enlace:

<x-mail::button :url="$actionUrl">
Ver estado del contrato
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
