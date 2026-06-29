@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Horaire</h1>
            <p class="es-page-subtitle">
                Semaine du {{ $grid['week_start']->translatedFormat('j F') }}
                au {{ $grid['week_end']->translatedFormat('j F Y') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-button href="{{ route('admin.schedules.index', ['week' => $prevWeek]) }}" variant="secondary" class="es-btn-sm">← Semaine</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => now()->startOfWeek()->toDateString()]) }}" variant="secondary" class="es-btn-sm">Aujourd'hui</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => $nextWeek]) }}" variant="secondary" class="es-btn-sm">Semaine →</x-button>
            <x-button type="button" class="es-btn-sm" @click="$dispatch('open-schedule-modal', { mode: 'specific', schedule_date: '{{ now()->toDateString() }}' })">+ Ajouter une date</x-button>
        </div>
    </div>

    <x-card class="overflow-x-auto mb-8">
        <div class="min-w-[960px]">
            <div class="es-schedule-grid-head">
                <div class="es-schedule-time-col"></div>
                @foreach ($grid['days'] as $day)
                    <div @class([
                        'es-schedule-day-head',
                        'es-schedule-day-today' => $day['is_today'],
                        'es-schedule-day-weekend' => $day['is_weekend'],
                    ])>
                        <span class="font-extrabold capitalize">{{ $day['short_label'] }}</span>
                    </div>
                @endforeach
            </div>

            @foreach ($grid['period_defs'] as $periodNumber => $periodDef)
                <div class="es-schedule-grid-row">
                    <div class="es-schedule-time-col">
                        <p class="font-bold text-es-ink text-sm">{{ $periodDef['label'] }}</p>
                        <p class="text-xs text-es-muted">{{ substr($periodDef['starts_at'], 0, 5) }}–{{ substr($periodDef['ends_at'], 0, 5) }}</p>
                    </div>
                    @foreach ($grid['days'] as $day)
                        @php $slot = $day['periods'][$periodNumber] ?? null; @endphp
                        <div @class([
                            'es-schedule-cell',
                            'es-schedule-cell-today' => $day['is_today'],
                            'es-schedule-cell-weekend' => $day['is_weekend'],
                        ])>
                            @if ($slot)
                                <button
                                    type="button"
                                    class="es-schedule-slot w-full text-left"
                                    style="--slot-color: {{ $slot['color'] }}"
                                    @click="$dispatch('open-schedule-modal', {
                                        id: {{ $slot['id'] }},
                                        mode: '{{ $slot['is_specific'] ? 'specific' : 'recurring' }}',
                                        subject_id: {{ $slot['subject_id'] }},
                                        title: @js($slot['title']),
                                        period_number: {{ $periodNumber }},
                                        day_of_week: {{ $day['day_of_week'] }},
                                        schedule_date: '{{ $slot['schedule_date'] ?? $day['date_key'] }}',
                                    })"
                                >
                                    <span class="es-schedule-slot-title">{{ $slot['title'] }}</span>
                                    <span class="es-schedule-slot-sub">{{ $slot['subject'] }}</span>
                                    @if ($slot['is_specific'])
                                        <span class="es-schedule-slot-badge">Date choisie</span>
                                    @else
                                        <span class="es-schedule-slot-badge es-schedule-slot-badge-recurring">Chaque semaine</span>
                                    @endif
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="es-schedule-empty"
                                    @click="$dispatch('open-schedule-modal', {
                                        mode: 'specific',
                                        period_number: {{ $periodNumber }},
                                        day_of_week: {{ $day['day_of_week'] }},
                                        schedule_date: '{{ $day['date_key'] }}',
                                    })"
                                >
                                    +
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-card>

    <p class="text-sm text-es-muted mb-8">
        Clique une case pour ajouter un cours sur <strong>cette date précise</strong>, ou choisis « Chaque semaine » pour un créneau récurrent.
        Tu peux planifier n'importe quel jour, y compris le week-end.
    </p>

    @if ($upcomingDates->isNotEmpty())
        <div class="mb-4 flex items-center justify-between gap-4">
            <h2 class="es-section-title !mb-0">Dates planifiées</h2>
            <x-button type="button" variant="secondary" class="es-btn-sm" @click="$dispatch('open-schedule-modal', { mode: 'specific', schedule_date: '{{ now()->toDateString() }}' })">+ Nouvelle date</x-button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($upcomingDates as $entry)
                @php
                    $periodDef = $grid['period_defs'][$entry->period_number] ?? null;
                @endphp
                <button
                    type="button"
                    class="es-schedule-date-card text-left"
                    style="--slot-color: {{ $entry->display_color }}"
                    @click="$dispatch('open-schedule-modal', {
                        id: {{ $entry->id }},
                        mode: 'specific',
                        subject_id: {{ $entry->subject_id }},
                        title: @js($entry->display_title),
                        period_number: {{ $entry->period_number }},
                        day_of_week: {{ $entry->day_of_week }},
                        schedule_date: '{{ $entry->schedule_date->toDateString() }}',
                    })"
                >
                    <p class="text-xs font-bold uppercase tracking-wide text-es-muted">
                        {{ $entry->schedule_date->translatedFormat('l j F Y') }}
                    </p>
                    <p class="font-extrabold text-es-ink mt-1">{{ $entry->display_title }}</p>
                    <p class="text-sm text-es-muted mt-1">
                        {{ $entry->subject?->name }}
                        @if ($periodDef)
                            · {{ $periodDef['label'] }} ({{ substr($periodDef['starts_at'], 0, 5) }}–{{ substr($periodDef['ends_at'], 0, 5) }})
                        @endif
                    </p>
                </button>
            @endforeach
        </div>
    @endif
</div>

<div
    x-data="scheduleModal(@js([
        'subjects' => $subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'color' => $s->color])->values(),
        'dayLabels' => $dayLabels,
        'periods' => $grid['period_defs'],
        'week' => $grid['week_start']->toDateString(),
        'storeUrl' => route('admin.schedules.store'),
        'updateUrl' => url('/admin/schedules'),
    ]))"
    x-on:open-schedule-modal.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-cloak
>
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-5" role="dialog" aria-modal="true">
        <div class="es-modal-backdrop" @click="close()"></div>
        <div class="es-modal-panel relative w-full max-w-md" @click.outside="close()">
            <h2 class="text-xl font-black text-es-ink mb-1" x-text="editing ? 'Modifier le créneau' : 'Nouveau créneau'"></h2>
            <p class="text-sm text-es-muted mb-5">Choisis la matière, la date et la période.</p>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="week" :value="week">

                <div>
                    <label class="es-label">Matière</label>
                    <select name="subject_id" x-model="form.subject_id" class="es-select" required>
                        <option value="">— Choisir —</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="es-label">Titre (optionnel)</label>
                    <input type="text" name="title" x-model="form.title" class="es-input" placeholder="Ex. Lecture, Fractions…">
                </div>

                <div>
                    <label class="es-label">Période</label>
                    <select name="period_number" x-model="form.period_number" class="es-select" required>
                        @foreach ($grid['period_defs'] as $num => $def)
                            <option value="{{ $num }}">{{ $def['label'] }} ({{ substr($def['starts_at'], 0, 5) }}–{{ substr($def['ends_at'], 0, 5) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="es-label">Planification</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="es-qtype-chip" :class="form.mode === 'specific' ? 'es-qtype-chip-active' : ''">
                            <input type="radio" name="mode" value="specific" x-model="form.mode" class="sr-only"> Date précise
                        </label>
                        <label class="es-qtype-chip" :class="form.mode === 'recurring' ? 'es-qtype-chip-active' : ''">
                            <input type="radio" name="mode" value="recurring" x-model="form.mode" class="sr-only"> Chaque semaine
                        </label>
                    </div>
                </div>

                <div x-show="form.mode === 'specific'" x-cloak>
                    <label class="es-label">Date du cours</label>
                    <input type="date" name="schedule_date" x-model="form.schedule_date" class="es-input" :required="form.mode === 'specific'">
                    <p class="text-xs text-es-muted mt-1">Tu choisis librement le jour — lundi, samedi, vacances, etc.</p>
                </div>

                <div x-show="form.mode === 'recurring'" x-cloak>
                    <label class="es-label">Jour (chaque semaine)</label>
                    <select name="day_of_week" x-model="form.day_of_week" class="es-select" :required="form.mode === 'recurring'">
                        @foreach ($dayLabels as $dow => $label)
                            <option value="{{ $dow }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <x-button type="submit" x-text="editing ? 'Enregistrer' : 'Ajouter'"></x-button>
                    <x-button type="button" variant="secondary" @click="close()">Annuler</x-button>
                </div>
            </form>

            <form x-show="editing" x-cloak :action="deleteUrl" method="POST" class="mt-3" onsubmit="return confirm('Supprimer ce créneau ?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="week" :value="week">
                <x-button type="submit" variant="danger">Supprimer ce créneau</x-button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function scheduleModal(config) {
    return {
        openModal: false,
        editing: false,
        week: config.week,
        formAction: config.storeUrl,
        deleteUrl: '',
        form: {
            subject_id: '',
            title: '',
            period_number: '1',
            mode: 'specific',
            day_of_week: '1',
            schedule_date: '',
        },
        open(detail = {}) {
            this.editing = Boolean(detail.id);
            this.formAction = this.editing ? `${config.updateUrl}/${detail.id}` : config.storeUrl;
            this.deleteUrl = this.editing ? `${config.updateUrl}/${detail.id}` : '';
            this.form = {
                subject_id: detail.subject_id ? String(detail.subject_id) : '',
                title: detail.title || '',
                period_number: String(detail.period_number || 1),
                mode: detail.mode || 'specific',
                day_of_week: String(detail.day_of_week || 1),
                schedule_date: detail.schedule_date || '',
            };
            this.openModal = true;
        },
        close() {
            this.openModal = false;
        },
    };
}
</script>
@endpush
@endsection
