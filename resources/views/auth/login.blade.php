<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-white">Iniciar Sesión en Tratix</h1>
        <p class="text-xs text-slate-400 mt-1">Accede a tus contratos, firmas y hojas de evidencias</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="tu@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-emerald-400 hover:text-emerald-300 transition" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-emerald-500 shadow-sm focus:ring-emerald-500" name="remember">
                <span class="ms-2 text-xs text-slate-400">Recordar mi sesión en este dispositivo</span>
            </label>
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 text-xs font-bold shadow-lg shadow-emerald-500/20">
                Iniciar Sesión
            </x-primary-button>
        </div>

        <div class="pt-4 border-t border-slate-700/80 text-center text-xs text-slate-400">
            ¿Aún no tienes una cuenta?
            <a href="{{ route('register') }}" class="text-emerald-400 font-semibold hover:underline">
                Regístrate gratis
            </a>
        </div>
    </form>
</x-guest-layout>
