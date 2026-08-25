<x-guest-layout>
    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-white">Verifica tu Correo Electrónico</h1>
        <p class="text-xs text-slate-400 mt-2 leading-relaxed">
            ¡Gracias por unirte a Tratix! Antes de comenzar a redactar contratos, por favor verifica tu dirección de correo haciendo clic en el enlace que te acabamos de enviar. Si no lo has recibido, con gusto te enviaremos otro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-xs text-emerald-400 bg-emerald-950/60 border border-emerald-800 p-3 rounded-xl">
            ✓ Se ha enviado un nuevo enlace de verificación a la dirección de correo que indicaste durante el registro.
        </div>
    @endif

    <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf

            <x-primary-button class="w-full sm:w-auto justify-center py-2 text-xs font-bold">
                Reenviar Correo de Verificación
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-xs text-slate-400 hover:text-slate-100 rounded-md focus:outline-none">
                Cerrar Sesión
            </button>
        </form>
    </div>
</x-guest-layout>
