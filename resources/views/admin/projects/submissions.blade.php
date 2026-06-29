@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <a href="{{ route('admin.projects.index') }}" class="es-link text-sm font-bold">← Projets</a>
    <h1 class="es-page-title mt-3">Soumissions — {{ $project->title }}</h1>
    <p class="es-page-subtitle">{{ $project->subject->name }}</p>

    <div class="mt-8 space-y-3">
        @forelse ($submissions as $submission)
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-extrabold">{{ $submission->student->full_name }}</p>
                    <p class="text-sm text-es-muted mt-1">
                        {{ $submission->statusLabel() }}
                        @if ($submission->submitted_at)
                            · {{ $submission->submitted_at->format('d/m/Y H:i') }}
                        @endif
                    </p>
                    @if ($submission->correction?->score !== null)
                        <p class="text-sm font-bold text-es-primary mt-1">{{ number_format($submission->correction->score, 0) }}/100</p>
                    @endif
                </div>
                @if (in_array($submission->workflow_status, ['submitted', 'corrected', 'returned'], true))
                    <x-button href="{{ route('admin.projects.corrections.show', [$project, $submission->student]) }}" variant="secondary">
                        {{ $submission->workflow_status === 'submitted' ? 'Corriger' : 'Voir' }}
                    </x-button>
                @endif
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune soumission pour l'instant</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
