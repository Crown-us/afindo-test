<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-900 antialiased">
        {{-- Layout halaman auth: form di kiri, foto di kanan (layar lebar). --}}
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="flex flex-col px-6 py-8 sm:px-12">
                <a href="{{ route('home') }}" class="self-start rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <x-brand />
                </a>
                <div class="flex flex-1 items-center justify-center py-10">
                    <div class="w-full max-w-sm">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="relative hidden lg:block">
                <img src="{{ asset('images/hero.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-gray-950/10 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-12 text-white">
                    <p class="max-w-md text-2xl font-semibold leading-snug">Belajar skill baru lewat video singkat, kapan pun kamu sempat.</p>
                    <p class="mt-3 text-sm text-white/70">Afindo Learn</p>
                </div>
            </div>
        </div>
    </body>
</html>
