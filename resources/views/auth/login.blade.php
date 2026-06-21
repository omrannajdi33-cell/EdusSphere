<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f7f4] flex items-center justify-center p-6">
    <x-card class="w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Connexion</h1>
        <p class="text-slate-600 text-sm mb-6">Authentification — Phase 4</p>
        <form class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5" placeholder="prof@edusphere.local" disabled>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
                <input type="password" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5" disabled>
            </div>
            <x-button type="button" class="w-full opacity-50 cursor-not-allowed" disabled>Se connecter (bientôt)</x-button>
        </form>
        <p class="mt-4 text-center"><a href="{{ url('/') }}" class="text-sm text-indigo-600">← Retour</a></p>
    </x-card>
</body>
</html>
