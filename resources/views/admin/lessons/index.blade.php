@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Leçons — {{ $activeLevel?->name ?? 'Tous niveaux' }}</h1>
            <p class="es-page-subtitle">{{ $lessons->total() }} leçon(s) · 📚 contenu officiel enrichi</p>
        </div>
        <x-button href="{{ route('admin.lessons.create') }}">+ Nouvelle leçon</x-button>
    </div>

    @if ($calendarLevels->isNotEmpty())
        <div class="es-tab-bar mb-6">
            @foreach ($calendarLevels as $level)
                <a
                    href="{{ route('admin.lessons.index', array_filter([
                        'level' => $level->id,
                        'q' => $filters['q'] ?: null,
                        'subject' => $filters['subject'] ?: null,
                        'category' => $filters['category'] ?: null,
                    ])) }}"
                    @class(['es-tab', 'es-tab-active' => $activeLevel && $level->id === $activeLevel->id])
                >{{ $level->name }}</a>
            @endforeach
        </div>
    @endif

    <x-card class="mb-8 !p-4">
        <form method="GET" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">
            @if ($activeLevel)
                <input type="hidden" name="level" value="{{ $activeLevel->id }}">
            @endif
            <div class="lg:col-span-2">
                <label class="es-label" for="lesson-search">Rechercher une leçon</label>
                <input
                    id="lesson-search"
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Ex. passé composé, fractions, territoire…"
                    class="es-input w-full"
                >
            </div>
            <div>
                <label class="es-label" for="lesson-subject">Matière</label>
                <select id="lesson-subject" name="subject" class="es-select w-full">
                    <option value="">Toutes les matières</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected($filters['subject'] == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="es-label" for="lesson-category">Catégorie</label>
                <select id="lesson-category" name="category" class="es-select w-full">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($filters['category'] === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2 lg:col-span-4">
                <x-button type="submit" class="es-btn-sm">🔍 Filtrer</x-button>
                <x-button href="{{ route('admin.lessons.index', ['level' => $activeLevel?->id]) }}" variant="secondary" class="es-btn-sm">Réinitialiser</x-button>
            </div>
        </form>
    </x-card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($lessons as $lesson)
            <div class="es-card p-5 flex flex-col es-lesson-card">
                <div class="flex items-start gap-3 mb-3">
                    <x-subject-icon :icon="$lesson->subject->icon" :color="$lesson->subject->color"/>
                    <div class="min-w-0 flex-1">
                        <p class="font-extrabold text-lg text-es-ink leading-snug">{{ $lesson->title }}</p>
                        <p class="text-sm text-es-muted mt-1">{{ $lesson->subject->name }} · {{ $lesson->skill->name }}</p>
                        @if ($lesson->category)
                            <span class="inline-block mt-2 text-xs font-bold px-2.5 py-1 rounded-full bg-stone-100 text-es-ink">{{ $lesson->category }}</span>
                        @endif
                    </div>
                    <x-status-badge :status="$lesson->status === 'published' ? 'published' : 'draft'" :label="$lesson->status === 'published' ? 'Publié' : 'Brouillon'"/>
                </div>
                @if ($lesson->description)
                    <p class="text-sm text-es-muted line-clamp-2 mb-4">{{ \Illuminate\Support\Str::limit(strip_tags(preg_replace('/^#+\s*/m', '', $lesson->description)), 140) }}</p>
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
            <div class="sm:col-span-2 xl:col-span-3 es-empty">
                <p class="font-extrabold text-es-ink">Aucune leçon trouvée</p>
                <p class="text-es-muted mt-2">Essaie un autre mot-clé ou réinitialise les filtres.</p>
            </div>
        @endforelse
    </div>

    @if ($lessons->hasPages())
        <div class="mt-8">{{ $lessons->links() }}</div>
    @endif
</div>
@endsection
