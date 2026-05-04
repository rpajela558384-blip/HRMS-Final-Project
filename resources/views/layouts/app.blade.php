<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'HRMS') }} @isset($title)— {{ $title }}@endisset</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {{-- Flash alerts --}}
                @if(session('success'))
                    <x-flash-alert type="success" :message="session('success')" />
                @endif
                @if(session('error'))
                    <x-flash-alert type="error" :message="session('error')" />
                @elseif($errors->any())
                    <x-flash-alert type="error" :message="$errors->first()" />
                @endif

                {{ $slot }}
            </main>
        </div>

        {{-- Confirmation modal portal --}}
        <div id="confirm-modal-backdrop"
             class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center"
             x-data x-cloak>
        </div>
    </body>
</html>
