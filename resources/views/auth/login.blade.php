@extends('layouts.guest')

@section('title', 'Connexion — '.config('app.name'))

@section('content')
<div class="relative min-h-screen flex">
    {{-- Panel gauche (tablette / desktop) --}}
    <div class="es-split-panel">
        <div class="es-split-blob-1"></div>
        <div class="es-split-blob-2"></div>
        <div class="es-split-content">
            <a href="{{ route('login') }}" class="flex items-center gap-4 group">
                <span class="es-logo-mark">E</span>
                <span class="text-3xl font-black text-white">EduSphere</span>
            </a>
            <h2 class="es-split-title">Ton espace d'apprentissage</h2>
            <p class="es-split-text">Connecte-toi pour accéder à tes leçons, activités et ta découverte du jour.</p>
            <ul class="mt-12 space-y-5">
                @foreach (['Découverte scientifique quotidienne', 'Activités interactives', 'Points & progression'] as $item)
                    <li class="es-check-item">
                        <span class="es-check-icon">✓</span>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Formulaire --}}
    <div class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="es-login-panel es-page-enter">
            <div class="lg:hidden mb-10">
                <x-logo subtitle="Connexion" />
            </div>

            <h1 class="text-4xl font-black text-es-ink">Connexion</h1>
            <p class="mt-3 text-xl font-medium text-es-muted">Email et mot de passe</p>

            @if ($errors->any())
                <div class="mt-7">
                    <x-alert type="error">{{ $errors->first() }}</x-alert>
                </div>
            @endif

            <form method="POST" action="/login" class="mt-9 space-y-6">
                @csrf

                <div>
                    <label for="email" class="es-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="{{ config('edusphere.login_email_placeholder') }}"
                        required
                        autofocus
                        class="es-input"
                    >
                </div>

                <div>
                    <label for="password" class="es-label">Mot de passe</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        class="es-input"
                    >
                </div>

                <label class="flex items-center gap-3.5 cursor-pointer">
                    <input type="checkbox" name="remember" class="es-checkbox">
                    <span class="text-base font-bold text-es-muted">Rester connecté</span>
                </label>

                <button type="submit" class="es-btn-primary w-full">
                    Se connecter
                </button>
            </form>

            <p class="mt-10 text-center">
                <a href="{{ route('home') }}" class="es-link text-base">← Retour à l'accueil</a>
            </p>
        </div>
    </div>
</div>
@endsection
