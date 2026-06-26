@extends('layouts.focus')

@section('title', $activity->title.' — '.config('app.name'))

@section('focus-content')
<div data-focus-room class="min-h-screen flex flex-col">
    <div data-focus-gate class="fixed inset-0 z-50 flex items-center justify-center bg-es-ink/90 p-6">
        <div class="es-card max-w-md w-full p-8 text-center space-y-4">
            <p class="text-4xl">🔒</p>
            <h1 class="text-2xl font-black text-es-ink">Mode salle d'examen</h1>
            <p class="text-es-muted">Tu restes sur cette page en plein écran. Tes réponses sont sauvegardées automatiquement.</p>
            <button type="button" class="es-btn-primary w-full" data-focus-start>Commencer</button>
        </div>
    </div>

    <div data-focus-warn class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-red-600 text-white px-5 py-3 rounded-2xl font-bold shadow-lg" role="alert"></div>

    <div data-focus-shell class="hidden flex-1 flex flex-col min-h-screen">
        <div class="flex-1 es-container py-4 max-w-4xl mx-auto w-full">
            @include('student.activities.partials.player-shell', [
                'activity' => $activity,
                'progression' => $progression,
                'answers' => $answers,
                'correction' => $correction ?? null,
                'previewMode' => false,
                'focusMode' => true,
                'examAttempt' => null,
                'lessonAnnotations' => $lessonAnnotations ?? collect(),
            ])
        </div>
    </div>
</div>
@endsection
