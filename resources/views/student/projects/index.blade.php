@extends('layouts.student')

@section('student-content')
<div class="es-page-enter es-container py-6">
    <h1 class="es-page-title">Mes projets</h1>
    <p class="es-page-subtitle">Travaux de recherche, comptes rendus et dossiers</p>

    <div class="mt-8 grid gap-4">
        @forelse ($projects as $project)
            @php
                $submission = $project->submissions->first();
                $status = $submission?->workflow_status ?? 'in_progress';
            @endphp
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span>{{ $project->typeIcon() }}</span>
                        <p class="font-extrabold text-lg truncate">{{ $project->title }}</p>
                    </div>
                    <p class="text-sm text-es-muted">{{ $project->subject->name }} · {{ $project->typeLabel() }}</p>
                    <p @class([
                        'text-sm font-bold mt-1',
                        'text-amber-600' => in_array($status, ['in_progress', 'returned']),
                        'text-emerald-600' => $status === 'corrected',
                        'text-es-primary' => $status === 'submitted',
                    ])>
                        {{ config('project.workflow_statuses.'.$status, $status) }}
                        @if ($project->due_at)
                            · Échéance {{ $project->due_at->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <x-button href="{{ route('student.projects.work', $project) }}" variant="secondary">
                    {{ in_array($status, ['submitted', 'corrected']) ? 'Voir' : 'Continuer' }}
                </x-button>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucun projet assigné</p>
                <p class="text-es-muted mt-2">Ton professeur te confiera des projets ici.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
