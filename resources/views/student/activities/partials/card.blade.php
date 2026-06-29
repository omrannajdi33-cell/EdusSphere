@props([
    'activity',
    'progress' => null,
    'correction' => null,
    'showHomeworkMeta' => false,
])

@php
    $prog = $progress;
    $corr = $correction;
    $student = auth()->user()->student;
    $overdue = $showHomeworkMeta && $student && $activity->isOverdueForStudent($student);
@endphp

<article @class(['es-card p-5 flex flex-col', 'es-homework-card-overdue' => $overdue])>
    <div class="flex items-center gap-3 mb-3">
        <x-subject-icon :icon="$activity->subject->icon" :color="$activity->subject->color" size="sm"/>
        <span class="text-sm font-bold text-es-muted">{{ $activity->subject->name }}</span>
        @if ($showHomeworkMeta && $overdue)
            <span class="ml-auto rounded-lg bg-red-100 px-2 py-0.5 text-[10px] font-black uppercase text-red-700">En retard</span>
        @endif
    </div>
    <h2 class="text-lg font-extrabold text-es-ink">{{ $activity->title }}</h2>
    <p class="text-sm text-es-muted mt-2 line-clamp-2">{{ $activity->description }}</p>

    @if ($showHomeworkMeta && $activity->due_at)
        <p @class([
            'text-sm font-bold mt-3',
            'text-red-600' => $overdue,
            'text-amber-700' => ! $overdue,
        ])>
            📅 À rendre avant le {{ $activity->due_at->translatedFormat('d M Y · H:i') }}
        </p>
    @endif

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
            {{ $showHomeworkMeta ? 'Faire le devoir' : 'Commencer' }}
        @endif
    </x-button>
</article>
