<nav x-data="{ open: false }" class="bg-slate-800 border-b border-slate-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="w-9 h-9" />
                        <span class="text-white font-extrabold text-lg tracking-tight">Tratix</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-300 hover:text-white hover:border-emerald-500 focus:outline-none focus:text-white transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'border-emerald-500 text-white' : '' }}">
                        Mis contratos
                    </a>
                    <a href="{{ route('contracts.create') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-300 hover:text-white hover:border-emerald-500 focus:outline-none focus:text-white transition duration-150 ease-in-out {{ request()->routeIs('contracts.create') ? 'border-emerald-500 text-white' : '' }}">
                        + Nuevo contrato
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-200 bg-slate-800 hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <button type="button" @click="window.dispatchEvent(new CustomEvent('open-tour'))" class="block w-full px-4 py-2 text-start text-sm leading-5 text-emerald-400 hover:bg-slate-700 hover:text-emerald-300 transition duration-150 ease-in-out font-semibold">
                            🎓 {{ __('Guía Rápida / Tutorial') }}
                        </button>
                        <x-dropdown-link :href="route('billing.pricing')">
                            {{ __('Planes') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('referrals.index')">
                            {{ __('Referir y ganar') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-700 focus:outline-none focus:bg-slate-700 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none {{ request()->routeIs('dashboard') ? 'border-emerald-500 text-white bg-slate-700' : '' }}">
                Mis contratos
            </a>
            <a href="{{ route('contracts.create') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none {{ request()->routeIs('contracts.create') ? 'border-emerald-500 text-white bg-slate-700' : '' }}">
                + Nuevo contrato
            </a>
            <a href="{{ route('billing.pricing') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none">
                Planes
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-slate-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-tour')); open = false" class="w-full text-left block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-semibold text-emerald-400 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none">
                    🎓 Guía Rápida / Tutorial
                </button>
                <a href="{{ route('profile.edit') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none">
                    Perfil
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="w-full text-left block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-emerald-500 focus:outline-none">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
