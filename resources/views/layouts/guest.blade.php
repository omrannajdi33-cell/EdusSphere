<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('pwa/manifest.json') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:500,600,700,800,900" rel="stylesheet">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="es-app-bg antialiased">
    <div class="es-bg-orbs" aria-hidden="true">
        <div class="es-bg-orb es-bg-orb-1"></div>
        <div class="es-bg-orb es-bg-orb-2"></div>
        <div class="es-bg-orb es-bg-orb-3"></div>
    </div>
    @yield('content')
    @stack('scripts')
</body>
</html>
