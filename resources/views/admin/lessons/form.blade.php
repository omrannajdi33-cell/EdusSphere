@extends('layouts.admin')

@php $isEdit = $lesson->exists; @endphp

@section('admin-content')
<div class="es-page-enter max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.lessons.index') }}" class="es-link text-sm">← Retour aux leçons</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier la leçon' : 'Nouvelle leçon' }}</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ $isEdit ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <x-input label="Titre" name="title" value="{{ old('title', $lesson->title) }}" required :error="$errors->first('title')"/>

            <div>
                <label for="description" class="es-label">Description</label>
                <textarea id="description" name="description" rows="4" class="es-textarea">{{ old('description', $lesson->description) }}</textarea>
            </div>

            @php
                $defaultSubjectId = old('subject_id', $lesson->subject_id ?? $subjects->first()?->id);
                $filteredSkills = $skills->where('subject_id', $defaultSubjectId);
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
                        <option value="{{ $skill->id }}" @selected(old('skill_id', $lesson->skill_id) == $skill->id)>{{ $skill->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="school_level_id" class="es-label">Niveau scolaire (optionnel)</label>
                <select id="school_level_id" name="school_level_id" class="es-select">
                    <option value="">Tous les niveaux</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(old('school_level_id', $lesson->school_level_id) == $level->id)>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <x-input label="Durée estimée (minutes)" name="estimated_duration_min" type="number" min="5" max="480" value="{{ old('estimated_duration_min', $lesson->estimated_duration_min) }}"/>

            <div class="flex gap-3 pt-2">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Créer la leçon' }}</x-button>
                <x-button href="{{ route('admin.lessons.index') }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>

    @if ($isEdit)
        <x-card class="mt-8">
            <h2 class="text-lg font-extrabold mb-2">Documents (PDF, PowerPoint)</h2>
            <p class="text-sm text-es-muted mb-4">Les élèves les consultent dans le lecteur EduSphere et peuvent annoter dessus.</p>

            @if ($lesson->mediaFiles->isNotEmpty())
                <ul class="space-y-2 mb-4">
                    @foreach ($lesson->mediaFiles as $media)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-stone-50 px-4 py-3">
                            <div class="flex-1 min-w-0">
                                <form method="POST" action="{{ route('admin.lessons.documents.update', [$lesson, $media]) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="label" value="{{ old('label_'.$media->id, $media->displayName()) }}" class="es-input es-input-sm flex-1 min-w-[12rem] font-semibold" maxlength="160" required>
                                    <x-button type="submit" variant="secondary" class="es-btn-sm">Renommer</x-button>
                                </form>
                                <p class="text-xs text-es-muted mt-1">{{ $media->filename }} · {{ strtoupper($media->source_kind ?? 'PDF') }}
                                    @if ($media->page_count) · {{ $media->page_count }} p. @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.lessons.documents.destroy', [$lesson, $media]) }}" onsubmit="return confirm('Supprimer ce document ?')">
                                @csrf @method('DELETE')
                                <x-button type="submit" variant="danger" class="es-btn-sm">Supprimer</x-button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.lessons.documents.store', $lesson) }}" enctype="multipart/form-data" class="space-y-4"
                x-data="{ rows: [{ id: 1 }] , nextId: 2 }">
                @csrf
                <div class="space-y-3">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] items-end rounded-xl border border-stone-200 bg-white p-4">
                            <div>
                                <label class="es-label">Nom affiché</label>
                                <input type="text" name="labels[]" class="es-input w-full" maxlength="160" placeholder="Ex. Cours du 12 mars (optionnel)">
                            </div>
                            <div>
                                <label class="es-label">Fichier (PDF, PPT, PPTX)</label>
                                <input type="file" name="documents[]" accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" class="es-input w-full" required>
                            </div>
                            <button type="button" class="es-btn es-btn-secondary es-btn-sm mb-0.5" x-show="rows.length > 1" @click="rows = rows.filter(r => r.id !== row.id)">Retirer</button>
                        </div>
                    </template>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm" @click="rows.push({ id: nextId++ })">+ Ajouter un autre document</button>
                    <x-button type="submit">Téléverser</x-button>
                </div>
            </form>
            @error('documents')<p class="es-field-error mt-2">{{ $message }}</p>@enderror
        </x-card>
    @else
        <p class="text-sm text-es-muted mt-6">Crée la leçon d'abord, puis ajoute PDF ou PowerPoint.</p>
    @endif
</div>

<script>
document.getElementById('subject_id')?.addEventListener('change', function () {
    const skills = @json($skills->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'subject_id' => $s->subject_id])->values());
    const select = document.getElementById('skill_id');
    const subjectId = this.value;
    select.innerHTML = '';
    skills.filter(s => String(s.subject_id) === String(subjectId)).forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        select.appendChild(opt);
    });
});
</script>
@endsection
