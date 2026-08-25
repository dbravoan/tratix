<x-guest-layout>
    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-white">Área Segura</h1>
        <p class="text-xs text-slate-400 mt-2 leading-relaxed">
            Esta es una sección protegida de la aplicación. Por favor, introduce tu contraseña para confirmar tu identidad antes de continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contraseña *" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            placeholder="Tu contraseña actual"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20">
                Confirmar y Continuar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
