@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full text-center space-y-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-indigo-600 text-white text-3xl font-bold shadow-lg">
            E
        </div>
        <div>
            <h1 class="text-4xl font-bold text-slate-900">EduSphere</h1>
            <p class="mt-3 text-slate-600 text-lg">Plateforme éducative interactive · Tablette first</p>
        </div>
        <p class="text-sm text-slate-500">Enfants 7–10 ans · HTML/Blade · PHP/Laravel · MySQL · PWA</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
            <x-button href="{{ url('/admin') }}" variant="primary">Espace professeur</x-button>
            <x-button href="{{ url('/student') }}" variant="secondary">Espace élève</x-button>
            <x-button href="{{ route('login') }}" variant="secondary">Connexion</x-button>
        </div>
        <p class="text-xs text-emerald-600 font-medium">Phase 1–2 · Application initialisée</p>
    </div>
</div>
@endsection
