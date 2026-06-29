@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter es-schedule-admin">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Horaire</h1>
            <p class="es-page-subtitle">
                Semaine du {{ $grid['week_start']->translatedFormat('j F') }}
                au {{ $grid['week_end']->translatedFormat('j F Y') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-button href="{{ route('admin.schedules.index', ['week' => $prevWeek]) }}" variant="secondary" class="es-btn-sm">←</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => now()->startOfWeek()->toDateString()]) }}" variant="secondary" class="es-btn-sm">Aujourd'hui</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => $nextWeek]) }}" variant="secondary" class="es-btn-sm">→</x-button>
            <x-button type="button" class="es-btn-sm" @click="$dispatch('open-schedule-modal', { mode: 'specific', schedule_date: '{{ now()->toDateString() }}' })">+ Date</x-button>
        </div>
    </div>

    <x-card class="overflow-x-auto mb-4 !p-4 md:!p-6">
        <div class="es-schedule-grid-wrap">
            <div class="es-schedule-grid-head">
                <div class="es-schedule-time-col"></div>
                @foreach ($grid['days'] as $day)
                    <div @class([
                        'es-schedule-day-head',
                        'es-schedule-day-today' => $day['is_today'],
                        'es-schedule-day-weekend' => $day['is_weekend'],
                    ])>
                        <span class="font-extrabold capitalize block">{{ $day['short_label'] }}</span>
                    </div>
                @endforeach
            </div>

            @foreach ($grid['period_defs'] as $periodNumber => $periodDef)
                <div class="es-schedule-grid-row">
                    <div class="es-schedule-time-col">
                        <p class="font-extrabold text-es-ink text-sm">P{{ $periodNumber }}</p>
                        <p class="text-xs text-es-muted leading-snug mt-0.5">{{ substr($periodDef['starts_at'], 0, 5) }}<br>{{ substr($periodDef['ends_at'], 0, 5) }}</p>
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
                                    class="es-schedule-slot-compact w-full"
                                    style="--slot-color: {{ $slot['color'] }}"
                                    title="{{ $slot['title'] }} · {{ $slot['time_label'] }}"
                                    @click="$dispatch('open-schedule-modal', @js([
                                        'id' => $slot['id'],
                                        'mode' => $slot['is_specific'] ? 'specific' : 'recurring',
                                        'subject_id' => $slot['subject_id'],
                                        'title' => $slot['title'],
                                        'period_number' => $periodNumber,
                                        'day_of_week' => $day['day_of_week'],
                                        'schedule_date' => $slot['schedule_date'] ?? $day['date_key'],
                                        'materials' => $slot['materials'] ?? '',
                                        'plan' => $slot['plan'] ?? '',
                                        'use_custom_time' => $slot['uses_custom_time'] ?? false,
                                        'starts_at' => substr((string) ($slot['starts_at'] ?? '08:30'), 0, 5),
                                        'ends_at' => substr((string) ($slot['ends_at'] ?? '09:45'), 0, 5),
                                        'activity_ids' => $slot['activity_ids'] ?? [],
                                        'exam_ids' => $slot['exam_ids'] ?? [],
                                    ]))"
                                >
                                    <span class="es-schedule-slot-compact-label">{{ $slot['grid_label'] }}</span>
                                    @if ($slot['has_notes'] ?? false)
                                        <span class="es-schedule-slot-note-dot" aria-hidden="true"></span>
                                    @endif
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="es-schedule-empty"
                                    aria-label="Ajouter un cours"
                                    @click="$dispatch('open-schedule-modal', {
                                        mode: 'specific',
                                        period_number: {{ $periodNumber }},
                                        day_of_week: {{ $day['day_of_week'] }},
                                        schedule_date: '{{ $day['date_key'] }}',
                                    })"
                                >+</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-card>

    <p class="text-sm text-es-muted">Clique un cours pour l'horaire, le matériel, les activités et examens liés.</p>
</div>

<div
    x-data="scheduleModal(@js([
        'dayLabels' => $dayLabels,
        'periods' => $grid['period_defs'],
        'week' => $grid['week_start']->toDateString(),
        'storeUrl' => route('admin.schedules.store'),
        'updateUrl' => url('/admin/schedules'),
        'activities' => $linkableActivities->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'subject_id' => $a->subject_id, 'subject' => $a->subject?->name])->values(),
        'exams' => $linkableExams->map(fn ($e) => ['id' => $e->id, 'title' => $e->title, 'subject_id' => $e->subject_id, 'subject' => $e->subject?->name])->values(),
    ]))"
    x-on:open-schedule-modal.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-cloak
>
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
        <div class="es-modal-backdrop" @click="close()"></div>
        <div class="es-schedule-modal relative w-full max-w-4xl max-h-[92vh] overflow-y-auto" @click.outside="close()">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-black text-es-ink" x-text="editing ? 'Détail du cours' : 'Planifier un cours'"></h2>
                    <p class="text-sm text-es-muted mt-1">Horaire, matériel, activités et examens pour cette période.</p>
                </div>
                <button type="button" class="rounded-xl p-2 text-es-muted hover:bg-stone-100 hover:text-es-ink" @click="close()" aria-label="Fermer">✕</button>
            </div>

            <form :action="formAction" method="POST">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="week" :value="week">

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
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
                            <label class="es-label">Titre du cours</label>
                            <input type="text" name="title" x-model="form.title" class="es-input" placeholder="Ex. Fractions, Lecture…">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="es-label">Période</label>
                                <select name="period_number" x-model="form.period_number" class="es-select" required>
                                    @foreach ($grid['period_defs'] as $num => $def)
                                        <option value="{{ $num }}">P{{ $num }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="es-label">Planification</label>
                                <select name="mode" x-model="form.mode" class="es-select" required>
                                    <option value="specific">Date précise</option>
                                    <option value="recurring">Chaque semaine</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="form.mode === 'specific'" x-cloak>
                            <label class="es-label">Date</label>
                            <input type="date" name="schedule_date" x-model="form.schedule_date" class="es-input" :required="form.mode === 'specific'">
                        </div>

                        <div x-show="form.mode === 'recurring'" x-cloak>
                            <label class="es-label">Jour</label>
                            <select name="day_of_week" x-model="form.day_of_week" class="es-select" :required="form.mode === 'recurring'">
                                @foreach ($dayLabels as $dow => $label)
                                    <option value="{{ $dow }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-2xl border border-stone-200 bg-stone-50/80 p-4 space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="use_custom_time" value="1" class="mt-1 rounded border-stone-300 text-es-primary focus:ring-es-primary" x-model="form.use_custom_time">
                                <span>
                                    <span class="font-extrabold text-es-ink block">Horaire personnalisé</span>
                                    <span class="text-xs text-es-muted" x-text="defaultTimeHint()"></span>
                                </span>
                            </label>
                            <div x-show="form.use_custom_time" x-cloak class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="es-label">Début</label>
                                    <input type="time" name="starts_at" x-model="form.starts_at" class="es-input" :required="form.use_custom_time">
                                </div>
                                <div>
                                    <label class="es-label">Fin</label>
                                    <input type="time" name="ends_at" x-model="form.ends_at" class="es-input" :required="form.use_custom_time">
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-es-muted" x-show="form.use_custom_time" x-cloak>
                                <span x-show="form.mode === 'recurring'">→ Appliqué <strong>toujours</strong> pour ce créneau hebdomadaire.</span>
                                <span x-show="form.mode === 'specific'">→ Horaire <strong>exceptionnel</strong> pour cette date seulement.</span>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="es-label">Matériel nécessaire</label>
                            <textarea name="materials" x-model="form.materials" class="es-textarea es-schedule-notes" rows="4" placeholder="Un élément par ligne"></textarea>
                        </div>
                        <div>
                            <label class="es-label">Ce que je planifie</label>
                            <textarea name="plan" x-model="form.plan" class="es-textarea es-schedule-notes" rows="4" placeholder="Un point par ligne"></textarea>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2 mt-6 pt-6 border-t border-stone-100">
                    <div>
                        <label class="es-label">Activités liées</label>
                        <p class="text-xs text-es-muted mb-3">Coche les activités prévues pendant ce cours.</p>
                        <div class="es-schedule-link-list max-h-44 overflow-y-auto">
                            <template x-for="activity in filteredActivities()" :key="'a-' + activity.id">
                                <label class="es-schedule-link-item">
                                    <input type="checkbox" name="activity_ids[]" :value="activity.id" :checked="isLinked('activity_ids', activity.id)" @change="toggleLink('activity_ids', activity.id)">
                                    <span class="min-w-0">
                                        <span class="font-bold text-es-ink block truncate" x-text="activity.title"></span>
                                        <span class="text-xs text-es-muted" x-text="activity.subject"></span>
                                    </span>
                                </label>
                            </template>
                            <p x-show="filteredActivities().length === 0" class="text-sm text-es-muted py-4 text-center">Aucune activité publiée pour cette matière.</p>
                        </div>
                    </div>
                    <div>
                        <label class="es-label">Examens liés</label>
                        <p class="text-xs text-es-muted mb-3">Coche les examens prévus pendant ce cours.</p>
                        <div class="es-schedule-link-list max-h-44 overflow-y-auto">
                            <template x-for="exam in filteredExams()" :key="'e-' + exam.id">
                                <label class="es-schedule-link-item">
                                    <input type="checkbox" name="exam_ids[]" :value="exam.id" :checked="isLinked('exam_ids', exam.id)" @change="toggleLink('exam_ids', exam.id)">
                                    <span class="min-w-0">
                                        <span class="font-bold text-es-ink block truncate" x-text="exam.title"></span>
                                        <span class="text-xs text-es-muted" x-text="exam.subject"></span>
                                    </span>
                                </label>
                            </template>
                            <p x-show="filteredExams().length === 0" class="text-sm text-es-muted py-4 text-center">Aucun examen disponible pour cette matière.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-stone-100">
                    <x-button type="submit" x-text="editing ? 'Enregistrer' : 'Ajouter'"></x-button>
                    <x-button type="button" variant="secondary" @click="close()">Fermer</x-button>
                </div>
            </form>

            <form x-show="editing" x-cloak :action="deleteUrl" method="POST" class="mt-3 flex justify-end" onsubmit="return confirm('Supprimer ce créneau ?')">
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
            materials: '',
            plan: '',
            use_custom_time: false,
            starts_at: '08:30',
            ends_at: '09:45',
            activity_ids: [],
            exam_ids: [],
        },
        defaultTimeHint() {
            const p = config.periods[this.form.period_number] || config.periods[1];
            if (!p) return '';
            return 'Par défaut : P' + this.form.period_number + ' · ' + p.starts_at.slice(0, 5) + '–' + p.ends_at.slice(0, 5);
        },
        filteredActivities() {
            if (!this.form.subject_id) return config.activities;
            return config.activities.filter((a) => String(a.subject_id) === String(this.form.subject_id));
        },
        filteredExams() {
            if (!this.form.subject_id) return config.exams;
            return config.exams.filter((e) => String(e.subject_id) === String(this.form.subject_id));
        },
        isLinked(field, id) {
            return this.form[field].map(String).includes(String(id));
        },
        toggleLink(field, id) {
            const key = String(id);
            const list = this.form[field].map(String);
            const index = list.indexOf(key);
            if (index >= 0) {
                this.form[field].splice(index, 1);
            } else {
                this.form[field].push(key);
            }
        },
        open(detail = {}) {
            this.editing = Boolean(detail.id);
            this.formAction = this.editing ? `${config.updateUrl}/${detail.id}` : config.storeUrl;
            this.deleteUrl = this.editing ? `${config.updateUrl}/${detail.id}` : '';
            const period = String(detail.period_number || 1);
            const periodDef = config.periods[period] || config.periods[1] || { starts_at: '08:30', ends_at: '09:45' };
            this.form = {
                subject_id: detail.subject_id ? String(detail.subject_id) : '',
                title: detail.title || '',
                period_number: period,
                mode: detail.mode || 'specific',
                day_of_week: String(detail.day_of_week || 1),
                schedule_date: detail.schedule_date || '',
                materials: detail.materials || '',
                plan: detail.plan || '',
                use_custom_time: Boolean(detail.use_custom_time),
                starts_at: detail.starts_at || periodDef.starts_at.slice(0, 5),
                ends_at: detail.ends_at || periodDef.ends_at.slice(0, 5),
                activity_ids: (detail.activity_ids || []).map(String),
                exam_ids: (detail.exam_ids || []).map(String),
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
