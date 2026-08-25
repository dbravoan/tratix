<x-guest-layout>
    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-white">Crear Cuenta Gratis en Tratix</h1>
        <p class="text-xs text-slate-400 mt-1">Redacta y firma contratos con plena validez legal</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nombre y Apellidos *" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" placeholder="Tu nombre completo" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico *" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="tu@email.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contraseña *" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            placeholder="Mínimo 8 caracteres"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Confirmar Contraseña *" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            placeholder="Repite tu contraseña"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- GDPR First Layer Notice --}}
        <div>
            <x-gdpr-info-box title="Protección de datos en tu registro (RGPD)" />
        </div>

        <div class="text-xs text-slate-400">
            Al registrarte confirmas que has leído y aceptas nuestra <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-400 underline font-semibold">Política de Privacidad y Protección de Datos</a>.
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20">
                Crear Cuenta Gratis
            </x-primary-button>
        </div>

        <div class="pt-4 border-t border-slate-700/80 text-center text-xs text-slate-400">
            ¿Ya tienes una cuenta registrada?
            <a href="{{ route('login') }}" class="text-emerald-400 font-semibold hover:underline">
                Iniciar sesión
            </a>
        </div>
    </form>
</x-guest-layout>
