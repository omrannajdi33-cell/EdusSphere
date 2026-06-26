@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes activités</h1>
        <p class="es-page-subtitle">Activités publiées par ton professeur</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($activities as $activity)
            @php
                $prog = $progress->get($activity->id);
                $corr = $corrections->get($activity->id);
            @endphp
            <article class="es-card p-5 flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <x-subject-icon :icon="$activity->subject->icon" :color="$activity->subject->color" size="sm"/>
                    <span class="text-sm font-bold text-es-muted">{{ $activity->subject->name }}</span>
                </div>
                <h2 class="text-lg font-extrabold text-es-ink">{{ $activity->title }}</h2>
                <p class="text-sm text-es-muted mt-2 line-clamp-2">{{ $activity->description }}</p>
                    @if ($prog)
                    <x-progress-bar :value="$prog->percent_complete" :max="100" label="Progression" class="mt-4"/>
                    @if ($prog->workflow_status === 'submitted')
                        <p class="text-sm font-bold text-amber-600 mt-2">Soumise — en attente de correction</p>
                    @elseif ($prog->workflow_status === 'returned')
                        <p class="text-sm font-bold text-amber-600 mt-2">Renvoyée — à modifier</p>
                    @elseif ($prog->workflow_status === 'corrected')
                        <p class="text-sm font-bold text-emerald-600 mt-2">
                            Corrigée ✓
                            @if ($corr?->score !== null)
                                · {{ number_format($corr->score, 0) }}/100
                            @endif
                        </p>
                    @endif
                @endif
                <x-button href="{{ route('student.activities.play', $activity) }}" class="mt-4 w-full">
                    @if ($prog?->workflow_status === 'returned')
                        Modifier ma copie
                    @elseif ($prog && $prog->percent_complete > 0 && $prog->workflow_status !== 'corrected')
                        Continuer
                    @elseif ($prog?->workflow_status === 'corrected')
                        Voir ma copie
                    @else
                        Commencer
                    @endif
                </x-button>
            </article>
        @empty
            <div class="sm:col-span-2 es-empty">
                <p class="font-extrabold">Aucune activité disponible</p>
                <p class="text-es-muted mt-2">Reviens plus tard !</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
