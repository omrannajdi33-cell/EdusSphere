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
            <x-button href="{{ route('admin.schedules.index', ['week' => $prevWeek]) }}" variant="secondary" class="es-btn-sm">←</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => now()->startOfWeek()->toDateString()]) }}" variant="secondary" class="es-btn-sm">Aujourd'hui</x-button>
            <x-button href="{{ route('admin.schedules.index', ['week' => $nextWeek]) }}" variant="secondary" class="es-btn-sm">→</x-button>
            <x-button type="button" class="es-btn-sm" @click="$dispatch('open-schedule-modal', { mode: 'specific', schedule_date: '{{ now()->toDateString() }}' })">+ Date</x-button>
        </div>
    </div>

    <x-card class="overflow-x-auto mb-6">
        <div class="min-w-[880px]">
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
                        <p class="font-bold text-es-ink text-xs">{{ $periodNumber }}</p>
                        <p class="text-[10px] text-es-muted leading-tight">{{ substr($periodDef['starts_at'], 0, 5) }}</p>
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
                                    title="{{ $slot['title'] }}"
                                    @click="$dispatch('open-schedule-modal', {
                                        id: {{ $slot['id'] }},
                                        mode: '{{ $slot['is_specific'] ? 'specific' : 'recurring' }}',
                                        subject_id: {{ $slot['subject_id'] }},
                                        title: @js($slot['title']),
                                        period_number: {{ $periodNumber }},
                                        day_of_week: {{ $day['day_of_week'] }},
                                        schedule_date: '{{ $slot['schedule_date'] ?? $day['date_key'] }}',
                                        materials: @js($slot['materials'] ?? ''),
                                        plan: @js($slot['plan'] ?? ''),
                                    })"
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

    <p class="text-sm text-es-muted mb-8">Clique un cours pour voir le détail, le matériel et ta planification.</p>

    @if ($upcomingDates->isNotEmpty())
        <h2 class="es-section-title">Dates à venir</h2>
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach ($upcomingDates as $entry)
                <button
                    type="button"
                    class="es-schedule-date-pill"
                    style="--slot-color: {{ $entry->display_color }}"
                    @click="$dispatch('open-schedule-modal', {
                        id: {{ $entry->id }},
                        mode: 'specific',
                        subject_id: {{ $entry->subject_id }},
                        title: @js($entry->display_title),
                        period_number: {{ $entry->period_number }},
                        day_of_week: {{ $entry->day_of_week }},
                        schedule_date: '{{ $entry->schedule_date->toDateString() }}',
                        materials: @js($entry->materials ?? ''),
                        plan: @js($entry->plan ?? ''),
                    })"
                >
                    <span>{{ $entry->schedule_date->translatedFormat('D j M') }}</span>
                    <span class="font-extrabold">{{ $entry->gridLabel() }}</span>
                </button>
            @endforeach
        </div>
    @endif
</div>

<div
    x-data="scheduleModal(@js([
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
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
        <div class="es-modal-backdrop" @click="close()"></div>
        <div class="es-schedule-modal relative w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.outside="close()">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-black text-es-ink" x-text="editing ? 'Détail du cours' : 'Planifier un cours'"></h2>
                    <p class="text-sm text-es-muted mt-1" x-show="editing" x-cloak>Modifie les infos, le matériel et ta planification.</p>
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
                                        <option value="{{ $num }}">P{{ $num }} · {{ substr($def['starts_at'], 0, 5) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="es-label">Répétition</label>
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
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="es-label">Matériel nécessaire</label>
                            <textarea
                                name="materials"
                                x-model="form.materials"
                                class="es-textarea es-schedule-notes"
                                rows="5"
                                placeholder="Un élément par ligne&#10;Ex.&#10;Cahier de maths&#10;Règle et équerre&#10;Crayons de couleur"
                            ></textarea>
                        </div>

                        <div>
                            <label class="es-label">Ce que je planifie</label>
                            <textarea
                                name="plan"
                                x-model="form.plan"
                                class="es-textarea es-schedule-notes"
                                rows="5"
                                placeholder="Un point par ligne&#10;Ex.&#10;Correction du devoir&#10;Leçon sur les fractions&#10;Exercices en autonomie"
                            ></textarea>
                        </div>

                        <div x-show="editing && (form.materials || form.plan)" x-cloak class="rounded-2xl bg-stone-50 p-4 text-sm space-y-3">
                            <template x-if="form.materials">
                                <div>
                                    <p class="font-bold text-es-muted text-xs uppercase mb-2">Aperçu matériel</p>
                                    <ul class="es-schedule-preview-list" x-html="previewList(form.materials)"></ul>
                                </div>
                            </template>
                            <template x-if="form.plan">
                                <div>
                                    <p class="font-bold text-es-muted text-xs uppercase mb-2">Aperçu plan</p>
                                    <ul class="es-schedule-preview-list" x-html="previewList(form.plan)"></ul>
                                </div>
                            </template>
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
        },
        previewList(text) {
            return String(text || '')
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter(Boolean)
                .map((line) => `<li>${line.replace(/</g, '&lt;')}</li>`)
                .join('');
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
                materials: detail.materials || '',
                plan: detail.plan || '',
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
