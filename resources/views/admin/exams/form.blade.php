@extends('layouts.admin')

@php $isEdit = $exam->exists; @endphp

@section('admin-content')
<div class="es-page-enter max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.exams.index') }}" class="es-link text-sm">← Retour aux examens</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier l\'examen' : 'Nouvel examen' }}</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ $isEdit ? route('admin.exams.update', $exam) : route('admin.exams.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <x-input label="Titre" name="title" value="{{ old('title', $exam->title) }}" required :error="$errors->first('title')"/>

            <x-input label="Description" name="description" value="{{ old('description', $exam->description) }}" />

            @php
                $defaultSubjectId = old('subject_id', $exam->subject_id ?? $subjects->first()?->id);
                $filteredSkills = $skills->where('subject_id', $defaultSubjectId);
                $filteredActivities = $activities->where('subject_id', $defaultSubjectId);
            @endphp

            <div>
                <label for="subject_id" class="es-label">Matière</label>
                <select id="subject_id" name="subject_id" class="es-select" required>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) $defaultSubjectId === (string) $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="skill_id" class="es-label">Compétence</label>
                <select id="skill_id" name="skill_id" class="es-select" required>
                    @foreach ($filteredSkills as $skill)
                        <option value="{{ $skill->id }}" @selected(old('skill_id', $exam->skill_id) == $skill->id)>{{ $skill->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="source_activity_id" class="es-label">Activité (contenu de l'examen)</label>
                <select id="source_activity_id" name="source_activity_id" class="es-select">
                    <option value="">— Choisir une activité publiée —</option>
                    @foreach ($filteredActivities as $activity)
                        <option value="{{ $activity->id }}" @selected(old('source_activity_id', $exam->source_activity_id) == $activity->id)>{{ $activity->title }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-es-muted mt-1">Les pages et questions de l'activité servent de sujet d'examen.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-input label="Durée (minutes)" name="duration_minutes" type="number" min="5" max="480" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required/>
                <x-input label="Tentatives max" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $exam->max_attempts) }}" required/>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-input label="Ouverture" name="opens_at" type="datetime-local" value="{{ old('opens_at', $exam->opens_at?->format('Y-m-d\TH:i')) }}" required/>
                <x-input label="Fermeture" name="closes_at" type="datetime-local" value="{{ old('closes_at', $exam->closes_at?->format('Y-m-d\TH:i')) }}" required/>
            </div>

            <div>
                <label for="status" class="es-label">Statut</label>
                <select id="status" name="status" class="es-select">
                    @foreach (config('exam.statuses') as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $exam->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Créer l\'examen' }}</x-button>
                <x-button href="{{ route('admin.exams.index') }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
document.getElementById('subject_id')?.addEventListener('change', function () {
    const subjectId = this.value;
    const skills = @json($skills->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'subject_id' => $s->subject_id])->values());
    const activities = @json($activities->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'subject_id' => $a->subject_id])->values());

    const fill = (selectId, items, labelKey) => {
        const select = document.getElementById(selectId);
        const current = select.value;
        select.innerHTML = selectId === 'source_activity_id' ? '<option value="">— Choisir —</option>' : '';
        items.filter(i => String(i.subject_id) === String(subjectId)).forEach(i => {
            const opt = document.createElement('option');
            opt.value = i.id;
            opt.textContent = i[labelKey];
            select.appendChild(opt);
        });
        if ([...select.options].some(o => o.value === current)) select.value = current;
    };

    fill('skill_id', skills, 'name');
    fill('source_activity_id', activities, 'title');
});
</script>
@endsection
