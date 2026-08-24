<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Tratix')) – {{ config('app.name', 'Tratix') }}</title>

        <meta name="description" content="Tratix · Contratos de compraventa con validez legal, firmados y sellados. España y América Latina.">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-900">
            @include('layouts.navigation')

            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-emerald-900/40 border border-emerald-700 text-emerald-200 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-red-900/40 border border-red-700 text-red-200 px-4 py-3 rounded-md text-sm">{{ session('error') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-red-900/40 border border-red-700 text-red-200 px-4 py-3 rounded-md text-sm">
                        <strong>Hay errores que revisar:</strong>
                        <ul class="list-disc ml-5 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-slate-800 shadow border-b border-slate-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>

            {{-- Interactive Onboarding Tour --}}
            <x-interactive-tour />
        </div>
    </body>
</html>
