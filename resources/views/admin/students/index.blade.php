@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Élèves</h1>
            <p class="es-page-subtitle">{{ $students->total() }} élève(s) inscrit(s)</p>
        </div>
        <x-button href="{{ route('admin.students.create') }}">+ Ajouter un élève</x-button>
    </div>

    <x-card class="mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <input type="search" name="q" value="{{ $search }}" placeholder="Rechercher par nom ou email…" class="es-input flex-1">
            <select name="level" class="es-select sm:w-48">
                <option value="">Tous les niveaux</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}" @selected($levelFilter == $level->id)>{{ $level->name }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrer</x-button>
        </form>
    </x-card>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($students as $student)
            <div class="es-card p-5 flex flex-col">
                <div class="flex items-center gap-4 mb-4">
                    <x-avatar
                        :name="$student->full_name"
                        :src="$student->avatarUrl('admin')"
                        size="lg"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="font-extrabold text-es-ink truncate">{{ $student->full_name }}</p>
                        <p class="text-sm text-es-muted truncate">{{ $student->user->email }}</p>
                        <p class="text-xs font-bold text-es-primary mt-1">
                            {{ $student->schoolLevel?->name ?? 'Sans niveau' }}
                            @if ($student->classGroup)
                                · {{ $student->classGroup->name }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-auto flex gap-2">
                    <x-button href="{{ route('admin.students.edit', $student) }}" variant="secondary" class="flex-1 es-btn-sm">Modifier</x-button>
                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Supprimer cet élève ?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                    </form>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 es-empty">
                <p class="font-extrabold text-es-ink">Aucun élève</p>
                <p class="text-es-muted mt-2">Ajoute ton premier élève pour commencer.</p>
            </div>
        @endforelse
    </div>

    @if ($students->hasPages())
        <div class="mt-8">{{ $students->links() }}</div>
    @endif
</div>
@endsection
