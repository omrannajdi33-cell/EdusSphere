@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter max-w-3xl">
    <h1 class="es-page-title">{{ $title ?? 'Section' }}</h1>
    <p class="es-page-subtitle">{{ $message ?? 'Cette section sera disponible dans une prochaine phase.' }}</p>

    <div class="es-empty mt-10">
        <div class="es-empty-icon">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-lg font-extrabold text-es-ink">Bientôt disponible</p>
        <p class="mt-2 text-base font-medium text-es-muted">Phase {{ $nextPhase ?? '7+' }}.</p>
        <x-button href="{{ route('admin.dashboard') }}" variant="secondary" class="mt-8">Retour au dashboard</x-button>
    </div>
</div>
@endsection
