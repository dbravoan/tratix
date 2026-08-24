<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Tratix') }} · {{ $title ?? 'Contratos legales firmados y sellados' }}</title>

        <meta name="description" content="Tratix · Contratos de compraventa con validez legal, firmados y sellados. España y América Latina.">

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900 px-4">
            <div>
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-10 h-10" />
                    <span class="text-2xl font-extrabold text-emerald-500">Tratix</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-slate-800 border border-slate-700 shadow-xl overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
            <p class="mt-6 text-xs text-slate-500">Tratix · Contratos con validez legal, firma electrónica y evidencias.</p>
        </div>

        {{-- Universal Cookie Consent --}}
        <x-cookie-consent />
    </body>
</html>
