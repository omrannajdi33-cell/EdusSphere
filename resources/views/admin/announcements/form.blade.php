@extends('layouts.admin')

@php $isEdit = $announcement->exists; @endphp

@section('admin-content')
<div class="es-page-enter max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.announcements.index') }}" class="es-link text-sm">← Retour aux annonces</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier l\'annonce' : 'Nouvelle annonce' }}</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ $isEdit ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <x-input label="Titre" name="title" value="{{ old('title', $announcement->title) }}" required :error="$errors->first('title')"/>

            <div>
                <label for="body" class="es-label">Message</label>
                <textarea id="body" name="body" rows="5" class="es-textarea" required>{{ old('body', $announcement->body) }}</textarea>
                @error('body')<p class="es-field-error">{{ $message }}</p>@enderror
            </div>

            @php $targetType = old('target_type', $announcement->target_type ?? 'all'); @endphp

            <div>
                <label class="es-label">Destinataires</label>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach (['all' => 'Tous', 'level' => 'Par niveau', 'student' => 'Un élève'] as $value => $label)
                        <label class="es-qtype-chip {{ $targetType === $value ? 'es-qtype-chip-active' : '' }}">
                            <input type="radio" name="target_type" value="{{ $value }}" @checked($targetType === $value) class="sr-only"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="target-level" class="{{ $targetType === 'level' ? '' : 'hidden' }}">
                <label for="target_level_id" class="es-label">Niveau scolaire</label>
                <select id="target_level_id" name="target_id" class="es-select" @disabled($targetType !== 'level')>
                    <option value="">— Choisir —</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(old('target_id', $announcement->target_id) == $level->id && $targetType === 'level')>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="target-student" class="{{ $targetType === 'student' ? '' : 'hidden' }}">
                <label for="target_student_id" class="es-label">Élève</label>
                <select id="target_student_id" class="es-select" @disabled($targetType !== 'student')>
                    <option value="">— Choisir —</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(old('target_id', $announcement->target_id) == $student->id && $targetType === 'student')>{{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>

            @unless ($isEdit && $announcement->published_at)
                <label class="flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="publish_now" value="1" class="es-checkbox" @checked(old('publish_now'))>
                    Publier immédiatement
                </label>
            @endunless

            <div class="flex gap-3 pt-2">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Créer l\'annonce' }}</x-button>
                <x-button href="{{ route('admin.announcements.index') }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
const radios = document.querySelectorAll('input[name="target_type"]');
const levelBox = document.getElementById('target-level');
const studentBox = document.getElementById('target-student');
const levelSelect = document.getElementById('target_level_id');
const studentSelect = document.getElementById('target_student_id');

function syncTarget() {
    const type = document.querySelector('input[name="target_type"]:checked')?.value;
    levelBox.classList.toggle('hidden', type !== 'level');
    studentBox.classList.toggle('hidden', type !== 'student');
    levelSelect.disabled = type !== 'level';
    studentSelect.disabled = type !== 'student';
    levelSelect.name = type === 'level' ? 'target_id' : '';
    studentSelect.name = type === 'student' ? 'target_id' : '';
}

radios.forEach(r => r.addEventListener('change', syncTarget));
syncTarget();
</script>
@endsection
