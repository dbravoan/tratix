<x-mail::message>
# 📜 Contrato Firmado y Sellado

Enhorabuena. El contrato **{{ $contract->reference }}** (*{{ $contract->object_description }}*) ha sido **firmado por ambas partes** y sellado con su correspondiente certificación de evidencias legales eIDAS y sellado de tiempo (TSA).

<x-mail::panel>
**Referencia:** {{ $contract->reference }}  
**Título:** {{ $contract->title }}  
**Estado:** Firmado y Custodiado  
**Firmantes:** {{ $contract->signatures->count() }} (Vendedor y Comprador)  
@if($contract->final_hash)
**Huella SHA-256:** `{{ Str::limit($contract->final_hash, 32) }}`
@endif
</x-mail::panel>

Se adjunta una copia oficial del documento firmado en formato PDF a este correo electrónico. También puedes descargar la versión definitiva y consultar la trazabilidad en cualquier momento desde el siguiente enlace:

<x-mail::button :url="$downloadUrl">
Descargar PDF firmado y evidencias
</x-mail::button>

*Nota legal: La hoja de evidencias incorporada al final del documento acredita la integridad criptográfica, direcciones IP y marcas de tiempo de las firmas conformes al Reglamento (UE) 910/2014 (eIDAS).*

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
