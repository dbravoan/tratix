<x-mail::message>
# 💬 Nuevo comentario en el contrato

**{{ $comment->author_name }}** ({{ ucfirst($comment->author_role) }}) ha añadido un nuevo comentario en el contrato **{{ $contract->reference }}** ({{ $contract->title }}).

@if($comment->clause_key)
> **Cláusula referenciada:** {{ $comment->clause_title ?? $comment->clause_key }}
@endif

<x-mail::panel>
"{{ $comment->content }}"
</x-mail::panel>

Puedes responder directamente o revisar los detalles del contrato en el siguiente enlace:

<x-mail::button :url="$actionUrl">
Ver contrato y responder
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
