<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-slate-800 to-teal-900 min-h-screen">
        <div class="min-h-screen flex flex-col items-center justify-center p-4">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-500 flex items-center justify-center font-bold text-white text-xl shadow-lg">HR</div>
                    <span class="font-bold text-3xl text-white tracking-wide">HRMS</span>
                </div>
                <p class="text-slate-400 text-sm">Human Resource Management System</p>
            </div>
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
            <p class="mt-6 text-xs text-slate-500">&copy; {{ date('Y') }} HRMS. All rights reserved.</p>
        </div>
    </body>
</html>
