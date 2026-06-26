@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes examens</h1>
        <p class="es-page-subtitle">À venir, en cours et terminés</p>
    </div>

    @if ($active->isNotEmpty())
        <h2 class="es-section-title">En cours</h2>
        <div class="space-y-3 mb-10">
            @foreach ($active as $exam)
                @php
                    $attempt = $attempts->get($exam->id)?->firstWhere('status', 'in_progress')
                        ?? $attempts->get($exam->id)?->first();
                    $canStart = $exam->canStudentStart($student->id);
                @endphp
                <div class="es-card p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="font-extrabold text-lg">{{ $exam->title }}</p>
                        <p class="text-sm text-es-muted">{{ $exam->subject->name }} · {{ $exam->duration_minutes }} min</p>
                        <p class="text-xs text-es-muted mt-1">Ferme le {{ $exam->closes_at->translatedFormat('j M à H:i') }}</p>
                    </div>
                    @if ($attempt?->status === 'in_progress')
                        <x-button href="{{ route('student.exams.take', $attempt) }}">Continuer</x-button>
                    @elseif ($canStart)
                        <form method="POST" action="{{ route('student.exams.start', $exam) }}">@csrf<x-button type="submit">Commencer</x-button></form>
                    @else
                        <span class="text-sm font-bold text-es-muted">Limite atteinte</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($upcoming->isNotEmpty())
        <h2 class="es-section-title">À venir</h2>
        <div class="space-y-3 mb-10">
            @foreach ($upcoming as $exam)
                <div class="es-card p-5 opacity-90">
                    <p class="font-extrabold">{{ $exam->title }}</p>
                    <p class="text-sm text-es-muted">{{ $exam->subject->name }}</p>
                    <p class="text-xs font-bold text-es-primary mt-2">Ouverture : {{ $exam->opens_at->translatedFormat('j M Y H:i') }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($finished->isNotEmpty())
        <h2 class="es-section-title">Terminés</h2>
        <div class="space-y-3">
            @foreach ($finished as $exam)
                @php $attempt = $attempts->get($exam->id)?->sortByDesc('finished_at')->first(); @endphp
                <div class="es-card p-5">
                    <p class="font-extrabold">{{ $exam->title }}</p>
                    <p class="text-sm text-es-muted">{{ $exam->subject->name }}</p>
                    @if ($attempt?->finished_at)
                        <p class="text-xs text-es-muted mt-2">Soumis le {{ $attempt->finished_at->translatedFormat('j M Y H:i') }}</p>
                    @endif
                    @if ($attempt?->status === 'corrected')
                        <x-button href="{{ route('student.bulletin.index') }}" variant="secondary" class="es-btn-sm mt-3">Voir ma note</x-button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($active->isEmpty() && $upcoming->isEmpty() && $finished->isEmpty())
        <x-card>
            <p class="text-center text-es-muted py-10 font-medium">Aucun examen pour le moment.</p>
        </x-card>
    @endif
</div>
@endsection
