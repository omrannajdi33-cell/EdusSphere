@extends('layouts.app')

@section('title', 'Hors ligne — EduSphere')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="es-empty max-w-md w-full">
        <div class="es-empty-icon mx-auto">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-es-ink">Pas de connexion</h1>
        <p class="mt-3 text-base font-medium text-es-muted">Vérifie ta connexion internet et réessaie.</p>
    </div>
</div>
@endsection
