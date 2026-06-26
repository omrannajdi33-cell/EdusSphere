@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter" x-data="{ tab: 'activities' }">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="es-page-title">Corrections</h1>
            <p class="es-page-subtitle">Activités et examens à corriger — une seule page</p>
        </div>
    </div>

    <div class="flex gap-2 mb-6 p-1 bg-stone-100 rounded-2xl w-fit">
        <button type="button"
            class="es-btn es-btn-sm"
            :class="tab === 'activities' ? 'es-btn-primary' : 'es-btn-secondary'"
            @click="tab = 'activities'">
            ✏️ Activités
            @if ($activityCorrections->count() > 0)
                <span class="ml-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-white/25 px-1.5 text-xs font-black">{{ $activityCorrections->count() }}</span>
            @endif
        </button>
        <button type="button"
            class="es-btn es-btn-sm"
            :class="tab === 'exams' ? 'es-btn-primary' : 'es-btn-secondary'"
            @click="tab = 'exams'">
            📝 Examens
            @if ($examCorrections->count() > 0)
                <span class="ml-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-white/25 px-1.5 text-xs font-black">{{ $examCorrections->count() }}</span>
            @endif
        </button>
    </div>

    <div x-show="tab === 'activities'" x-cloak class="space-y-3">
        @forelse ($activityCorrections as $correction)
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-extrabold text-lg">{{ $correction->student->full_name }}</p>
                    <p class="text-sm text-es-muted mt-1">
                        {{ $correction->activity->title }}
                        · {{ $correction->activity->subject->name ?? '' }}
                    </p>
                    <p class="text-sm font-semibold text-amber-600 mt-1">
                        {{ config('activity.correction_statuses.'.$correction->status, $correction->status) }}
                        · {{ $correction->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <x-button href="{{ route('admin.activities.corrections.show', [$correction->activity, $correction->student]) }}" variant="secondary">
                    Corriger
                </x-button>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune activité à corriger</p>
                <p class="text-es-muted mt-2">Les nouvelles soumissions apparaîtront ici.</p>
            </div>
        @endforelse
    </div>

    <div x-show="tab === 'exams'" x-cloak class="space-y-3" style="display: none;">
        @forelse ($examCorrections as $correction)
            @php $attempt = $correction->examAttempt; @endphp
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-extrabold text-lg">{{ $correction->student->full_name }}</p>
                    <p class="text-sm text-es-muted mt-1">
                        {{ $attempt?->exam->title }}
                        · {{ $attempt?->exam->subject->name ?? '' }}
                    </p>
                    <p class="text-sm font-semibold text-amber-600 mt-1">
                        {{ config('activity.correction_statuses.'.$correction->status, $correction->status) }}
                        · {{ $correction->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                @if ($attempt)
                    <x-button href="{{ route('admin.exams.attempts.correct', $attempt) }}" variant="secondary">
                        Corriger
                    </x-button>
                @endif
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucun examen à corriger</p>
                <p class="text-es-muted mt-2">Les examens avec questions ouvertes apparaîtront ici après soumission.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
