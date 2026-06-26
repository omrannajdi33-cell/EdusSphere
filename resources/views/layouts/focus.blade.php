<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/focus-room.js', 'resources/js/document-viewer.js', 'resources/js/activity-player.js'])
    @stack('head')
</head>
<body class="es-app-bg es-focus-body overflow-hidden">
    @yield('focus-content')
    @stack('scripts')
</body>
</html>
