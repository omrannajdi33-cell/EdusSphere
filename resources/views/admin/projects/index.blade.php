@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
        <div>
            <h1 class="es-page-title">Projets</h1>
            <p class="es-page-subtitle">Travaux de recherche, comptes rendus et dossiers avec sources & bibliographie</p>
        </div>
        <x-button href="{{ route('admin.projects.create') }}">+ Créer un projet</x-button>
    </div>

    <x-card class="mb-8 !p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="subject" class="es-select sm:w-48">
                <option value="">Toutes matières</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($subjectFilter == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <select name="status" class="es-select sm:w-40">
                <option value="">Tous statuts</option>
                @foreach (config('project.statuses') as $key => $label)
                    <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrer</x-button>
        </form>
    </x-card>

    <div class="grid gap-4">
        @forelse ($projects as $project)
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-lg">{{ $project->typeIcon() }}</span>
                        <p class="font-extrabold text-lg truncate">{{ $project->title }}</p>
                        <span class="es-badge">{{ config('project.statuses.'.$project->status, $project->status) }}</span>
                    </div>
                    <p class="text-sm text-es-muted">
                        {{ $project->subject->name }}
                        · {{ $project->typeLabel() }}
                        · {{ $project->formatLabel() }}
                        @if ($project->due_at)
                            · Échéance {{ $project->due_at->format('d/m/Y') }}
                        @endif
                    </p>
                    <p class="text-xs text-es-muted mt-1">{{ $project->submissions_count }} soumission(s)</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button href="{{ route('admin.projects.build', ['project' => $project, 'step' => 1]) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                    @if ($project->isPublished())
                        <x-button href="{{ route('admin.projects.submissions', $project) }}" variant="secondary" class="es-btn-sm">Soumissions</x-button>
                    @endif
                </div>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucun projet pour l'instant</p>
                <p class="text-es-muted mt-2">Crée un projet avec consignes, pièces jointes et type de rendu.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $projects->links() }}</div>
</div>
@endsection
