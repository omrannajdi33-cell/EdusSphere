@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="es-page-title">Mon horaire</h1>
            <p class="es-page-subtitle">
                @if ($view === 'day')
                    {{ $reference->translatedFormat('l j F Y') }}
                @elseif ($view === 'month')
                    {{ $reference->translatedFormat('F Y') }}
                @else
                    Semaine du {{ $weekGrid['week_start']->translatedFormat('j F') }}
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <x-button href="{{ route('student.schedule.index', ['view' => $view, 'date' => $prevDate]) }}" variant="secondary" class="es-btn-sm">←</x-button>
            <x-button href="{{ route('student.schedule.index', ['view' => $view, 'date' => now()->toDateString()]) }}" variant="secondary" class="es-btn-sm">Aujourd'hui</x-button>
            <x-button href="{{ route('student.schedule.index', ['view' => $view, 'date' => $nextDate]) }}" variant="secondary" class="es-btn-sm">→</x-button>
        </div>
    </div>

    <div class="es-tab-bar mb-6">
        @foreach ([
            'day' => 'Jour',
            'week' => 'Semaine',
            'month' => 'Mois',
        ] as $tabId => $tabLabel)
            <a
                href="{{ route('student.schedule.index', ['view' => $tabId, 'date' => $reference->toDateString()]) }}"
                @class(['es-tab', 'es-tab-active' => $view === $tabId])
            >{{ $tabLabel }}</a>
        @endforeach
    </div>

    @if ($view === 'day')
        <div class="space-y-3">
            @php $hasCourses = collect($dayPeriods)->filter()->isNotEmpty(); @endphp
            @if ($hasCourses)
                @foreach ($periodDefs as $periodNumber => $periodDef)
                    @php $slot = $dayPeriods[$periodNumber] ?? null; @endphp
                    @if ($slot)
                        <div class="es-schedule-day-card" style="border-left-color: {{ $slot['color'] }}">
                            <div class="min-w-[4.5rem] text-center">
                                <p class="text-xs font-bold uppercase text-es-muted">{{ $periodDef['label'] }}</p>
                                <p class="text-sm font-extrabold text-es-ink">{{ substr($slot['starts_at'], 0, 5) }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold text-lg text-es-ink">{{ $slot['title'] }}</p>
                                <p class="text-sm text-es-muted">{{ $slot['subject'] }} · {{ substr($slot['starts_at'], 0, 5) }}–{{ substr($slot['ends_at'], 0, 5) }}</p>
                                @if (! empty($slot['activities']) || ! empty($slot['exams']))
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach ($slot['activities'] ?? [] as $activity)
                                            <a href="{{ route('student.activities.play', $activity['id']) }}" class="es-schedule-student-link">✏️ {{ $activity['title'] }}</a>
                                        @endforeach
                                        @foreach ($slot['exams'] ?? [] as $exam)
                                            <span class="es-schedule-student-link">📝 {{ $exam['title'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <x-card>
                    <p class="text-base font-medium text-es-muted text-center py-10">Aucun cours prévu ce jour-là 🎉</p>
                </x-card>
            @endif
        </div>
    @elseif ($view === 'week')
        <x-card class="overflow-x-auto">
            <div class="min-w-[640px] space-y-2">
                @foreach ($weekGrid['days'] as $day)
                    <div @class([
                        'rounded-2xl p-4',
                        'bg-stone-50' => $day['is_today'],
                        'opacity-90' => ($day['is_weekend'] ?? false) && ! $day['is_today'],
                    ])>
                        <p @class([
                            'font-extrabold capitalize mb-3',
                            'text-es-primary' => $day['is_today'],
                            'text-es-muted' => ($day['is_weekend'] ?? false) && ! $day['is_today'],
                        ])>{{ $day['label'] }}</p>
                        <div class="space-y-2">
                            @if (collect($day['periods'])->filter()->isNotEmpty())
                                @foreach ($periodDefs as $periodNumber => $periodDef)
                                    @php $slot = $day['periods'][$periodNumber] ?? null; @endphp
                                    @if ($slot)
                                        <div class="flex items-center gap-3 rounded-xl px-3 py-2" style="background: color-mix(in srgb, {{ $slot['color'] }} 12%, white); border-left: 3px solid {{ $slot['color'] }}">
                                            <span class="text-xs font-bold text-es-muted w-16">{{ substr($slot['starts_at'], 0, 5) }}</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-bold text-es-ink">{{ $slot['title'] }}</span>
                                                @if (! empty($slot['activities']) || ! empty($slot['exams']))
                                                    <span class="text-xs text-es-muted block mt-0.5">
                                                        @if (! empty($slot['activities']))
                                                            {{ count($slot['activities']) }} activité(s)
                                                        @endif
                                                        @if (! empty($slot['exams']))
                                                            · {{ count($slot['exams']) }} examen(s)
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-sm text-es-muted">Pas de cours</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @else
        <x-calendar :month="$month" :year="$year" :events="$monthEvents"/>
        <p class="text-sm text-es-muted mt-4 text-center">Les points indiquent les jours avec des cours.</p>
    @endif
</div>
@endsection
