@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Matières</h1>
            <p class="es-page-subtitle">{{ $subjects->count() }} matière(s)</p>
        </div>
        <x-button href="{{ route('admin.subjects.create') }}">+ Ajouter une matière</x-button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($subjects as $subject)
            <div class="es-card p-5">
                <div class="flex items-start gap-4 mb-4">
                    <x-subject-icon :icon="$subject->icon" :color="$subject->color"/>
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-lg text-es-ink">{{ $subject->name }}</p>
                        <p class="text-sm text-es-muted">{{ $subject->skills_count }} compétence(s)</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button href="{{ route('admin.subjects.skills.index', $subject) }}" variant="secondary" class="es-btn-sm">Compétences</x-button>
                    <x-button href="{{ route('admin.notions.index', ['subject' => $subject->id]) }}" variant="secondary" class="es-btn-sm">Notions</x-button>
                    <x-button href="{{ route('admin.subjects.edit', $subject) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Supprimer cette matière et ses compétences ?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
