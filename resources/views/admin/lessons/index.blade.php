@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Leçons</h1>
            <p class="es-page-subtitle">{{ $lessons->total() }} leçon(s)</p>
        </div>
        <x-button href="{{ route('admin.lessons.create') }}">+ Nouvelle leçon</x-button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($lessons as $lesson)
            <div class="es-card p-5 flex flex-col">
                <div class="flex items-start gap-3 mb-3">
                    <x-subject-icon :icon="$lesson->subject->icon" :color="$lesson->subject->color"/>
                    <div class="min-w-0 flex-1">
                        <p class="font-extrabold text-lg text-es-ink truncate">{{ $lesson->title }}</p>
                        <p class="text-sm text-es-muted">{{ $lesson->subject->name }} · {{ $lesson->skill->name }}</p>
                        @if ($lesson->schoolLevel)
                            <p class="text-xs font-bold text-es-primary mt-1">{{ $lesson->schoolLevel->name }}</p>
                        @endif
                    </div>
                    <x-status-badge :status="$lesson->status === 'published' ? 'published' : 'draft'" :label="$lesson->status === 'published' ? 'Publié' : 'Brouillon'"/>
                </div>
                @if ($lesson->description)
                    <p class="text-sm text-es-muted line-clamp-2 mb-4">{{ $lesson->description }}</p>
                @endif
                <div class="mt-auto flex flex-wrap gap-2">
                    <x-button href="{{ route('admin.lessons.edit', $lesson) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                    @if ($lesson->status !== 'published')
                        <form method="POST" action="{{ route('admin.lessons.publish', $lesson) }}">@csrf<x-button type="submit" class="es-btn-sm">Publier</x-button></form>
                    @else
                        <form method="POST" action="{{ route('admin.lessons.unpublish', $lesson) }}">@csrf<x-button type="submit" variant="secondary" class="es-btn-sm">Dépublier</x-button></form>
                    @endif
                    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Supprimer cette leçon ?')">
                        @csrf @method('DELETE')
                        <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                    </form>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 es-empty">
                <p class="font-extrabold text-es-ink">Aucune leçon</p>
                <p class="text-es-muted mt-2">Crée ta première leçon pour tes élèves.</p>
            </div>
        @endforelse
    </div>

    @if ($lessons->hasPages())
        <div class="mt-8">{{ $lessons->links() }}</div>
    @endif
</div>
@endsection
