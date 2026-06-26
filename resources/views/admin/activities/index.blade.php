@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
        <div>
            <h1 class="es-page-title">Activités</h1>
            <p class="es-page-subtitle">Crée des activités en 3 étapes : infos → contenu → publication</p>
        </div>
        <x-button href="{{ route('admin.activities.create') }}">+ Créer une activité</x-button>
    </div>

    {{-- Guide rapide --}}
    <div class="es-activity-guide mb-10">
        <div class="es-activity-guide-item">
            <span class="es-activity-guide-num">1</span>
            <div>
                <p class="font-extrabold">Informations</p>
                <p class="text-sm text-es-muted">Titre, matière, compétence</p>
            </div>
        </div>
        <div class="es-activity-guide-arrow" aria-hidden="true">→</div>
        <div class="es-activity-guide-item">
            <span class="es-activity-guide-num">2</span>
            <div>
                <p class="font-extrabold">Contenu</p>
                <p class="text-sm text-es-muted">PDF, écriture ou 10 types de questions</p>
            </div>
        </div>
        <div class="es-activity-guide-arrow" aria-hidden="true">→</div>
        <div class="es-activity-guide-item">
            <span class="es-activity-guide-num">3</span>
            <div>
                <p class="font-extrabold">Publication</p>
                <p class="text-sm text-es-muted">Aperçu et mise en ligne</p>
            </div>
        </div>
    </div>

    <x-card class="mb-8 !p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3" aria-label="Filtrer">
            <select name="subject" class="es-select sm:w-48">
                <option value="">Toutes matières</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($subjectFilter == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <select name="status" class="es-select sm:w-40">
                <option value="">Tous statuts</option>
                @foreach (config('activity.statuses') as $key => $label)
                    <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrer</x-button>
        </form>
    </x-card>

    <div class="grid gap-5 md:grid-cols-2">
        @forelse ($activities as $activity)
            <article class="es-activity-card">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <x-status-badge :status="match($activity->status) { 'published' => 'published', default => 'draft' }" :label="config('activity.statuses.'.$activity->status)"/>
                    <span class="text-xs font-bold text-es-muted">{{ $activity->pages_count }} étape(s)</span>
                </div>
                <h2 class="text-xl font-extrabold text-es-ink leading-tight">{{ $activity->title }}</h2>
                <p class="text-sm font-semibold text-es-muted mt-2">{{ $activity->subject->name }} · {{ $activity->skill->name }}</p>

                <div class="flex flex-wrap gap-2 mt-6 pt-4 border-t border-stone-100">
                    @if ($activity->status === 'draft')
                        <x-button href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => $activity->pages_count ? 2 : 1]) }}" class="es-btn-sm flex-1">
                            {{ $activity->pages_count ? 'Continuer' : 'Commencer' }}
                        </x-button>
                    @else
                        <x-button href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 2]) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                    @endif
                    <x-button href="{{ route('admin.activities.submissions', $activity) }}" variant="secondary" class="es-btn-sm">Copies</x-button>
                    <x-button href="{{ route('admin.activities.preview', $activity) }}" variant="secondary" class="es-btn-sm">Aperçu</x-button>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 es-empty py-16">
                <p class="text-4xl mb-3">📝</p>
                <p class="font-extrabold text-xl">Aucune activité</p>
                <p class="text-es-muted mt-2 mb-6">Crée ta première activité en quelques clics.</p>
                <x-button href="{{ route('admin.activities.create') }}">+ Créer une activité</x-button>
            </div>
        @endforelse
    </div>

    @if ($activities->hasPages())
        <div class="mt-8">{{ $activities->links() }}</div>
    @endif
</div>
@endsection
