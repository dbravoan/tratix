<x-guest-layout>
    <div class="mb-4 text-center">
        <h1 class="text-xl font-bold text-white">¿Olvidaste tu Contraseña?</h1>
        <p class="text-xs text-slate-400 mt-1">
            No te preocupes. Indica tu correo electrónico y te enviaremos un enlace seguro para que puedas restablecerla de inmediato.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico *" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="tu@email.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-primary-button class="w-full justify-center py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20">
                Enviar Enlace de Restablecimiento
            </x-primary-button>
        </div>

        <div class="pt-4 border-t border-slate-700/80 text-center text-xs text-slate-400">
            ¿Recordaste tu contraseña?
            <a href="{{ route('login') }}" class="text-emerald-400 font-semibold hover:underline">
                Volver a Iniciar Sesión
            </a>
        </div>
    </form>
</x-guest-layout>
