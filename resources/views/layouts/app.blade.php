<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('pwa/manifest.json') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:600,700,800&display=swap" rel="stylesheet">
    <title>@yield('title', config('app.name'))</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @stack('head')
</head>
<body class="es-app-bg">
    <div class="es-bg-orbs" aria-hidden="true">
        <div class="es-bg-orb es-bg-orb-1"></div>
        <div class="es-bg-orb es-bg-orb-2"></div>
        <div class="es-bg-orb es-bg-orb-3"></div>
    </div>

    @if (session('success'))
        <div class="fixed top-5 left-1/2 z-50 -translate-x-1/2 px-5 w-full max-w-md es-animate-in">
            <x-alert type="success">{{ session('success') }}</x-alert>
        </div>
    @endif
    @if (session('info'))
        <div class="fixed top-5 left-1/2 z-50 -translate-x-1/2 px-5 w-full max-w-md es-animate-in">
            <x-alert type="info">{{ session('info') }}</x-alert>
        </div>
    @endif
    @yield('content')
    @stack('scripts')
</body>
</html>
