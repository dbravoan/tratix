<x-guest-layout>
    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-white">Restablecer tu Contraseña</h1>
        <p class="text-xs text-slate-400 mt-1">Introduce tu nueva clave de acceso para tu cuenta de Tratix</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico *" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Nueva Contraseña *" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Confirmar Nueva Contraseña *" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" placeholder="Repite la nueva contraseña" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20">
                Guardar Nueva Contraseña
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
