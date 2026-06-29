@extends('layouts.admin')

@php $isNew = ! $project->exists; @endphp

@section('admin-content')
<div class="es-page-enter es-wizard-page">
    <div class="mb-6">
        <a href="{{ route('admin.projects.index') }}" class="es-link text-sm font-bold">← Mes projets</a>
        <h1 class="es-page-title mt-3">{{ $isNew ? 'Créer un projet' : 'Modifier le projet' }}</h1>
        @unless ($isNew)
            <p class="es-page-subtitle">{{ $project->title }}</p>
        @endunless
    </div>

    <x-project-wizard-nav :step="$step" :project="$project"/>

    @if ($step === 1)
        <div class="es-wizard-panel max-w-2xl">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">1</span>
                <div>
                    <h2 class="text-2xl font-black text-es-ink">Informations du projet</h2>
                    <p class="text-es-muted mt-1">Titre, matière, type de projet et mode de rendu.</p>
                </div>
            </div>

            <form method="POST" action="{{ $isNew ? route('admin.projects.store') : route('admin.projects.update', $project) }}" class="space-y-6 mt-8">
                @csrf
                @unless ($isNew) @method('PUT') @endunless

                <x-input label="Titre du projet" name="title" value="{{ old('title', $project->title) }}" required :error="$errors->first('title')"/>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="subject_id" class="es-label">Matière</label>
                        <select id="subject_id" name="subject_id" class="es-select" required>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $project->subject_id) == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="skill_id" class="es-label">Compétence (optionnel)</label>
                        <select id="skill_id" name="skill_id" class="es-select">
                            <option value="">— Aucune —</option>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" @selected(old('skill_id', $project->skill_id) == $skill->id)>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="es-label">Type de projet</label>
                    <div class="grid gap-3 sm:grid-cols-2 mt-2">
                        @foreach (config('project.project_types') as $key => $meta)
                            <label @class(['ap-type-menu-item cursor-pointer', 'ap-type-menu-item-active' => old('project_type', $project->project_type ?? 'research') === $key])>
                                <input type="radio" name="project_type" value="{{ $key }}" class="sr-only" @checked(old('project_type', $project->project_type ?? 'research') === $key)>
                                <span class="ap-type-menu-icon">{{ $meta['icon'] }}</span>
                                <span>
                                    <span class="ap-type-menu-label">{{ $meta['label'] }}</span>
                                    <span class="ap-type-menu-desc">{{ $meta['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="es-label">Rendu attendu de l'élève</label>
                    <div class="grid gap-3 sm:grid-cols-3 mt-2">
                        @foreach (config('project.submission_formats') as $key => $meta)
                            <label @class(['ap-type-menu-item cursor-pointer', 'ap-type-menu-item-active' => old('submission_format', $project->submission_format ?? 'both') === $key])>
                                <input type="radio" name="submission_format" value="{{ $key }}" class="sr-only" @checked(old('submission_format', $project->submission_format ?? 'both') === $key)>
                                <span class="ap-type-menu-icon">{{ $meta['icon'] }}</span>
                                <span>
                                    <span class="ap-type-menu-label">{{ $meta['label'] }}</span>
                                    <span class="ap-type-menu-desc">{{ $meta['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="due_at" class="es-label">Date limite (optionnel)</label>
                    <input type="datetime-local" id="due_at" name="due_at" class="es-input"
                        value="{{ old('due_at', $project->due_at?->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="rounded-2xl border border-stone-200 bg-stone-50/80 p-5 space-y-3">
                    <p class="font-extrabold text-es-ink">Sections obligatoires pour l'élève</p>
                    <label class="flex items-center gap-2 text-sm font-semibold">
                        <input type="checkbox" name="require_sources" value="1" class="es-checkbox" @checked(old('require_sources', $project->require_sources ?? true))>
                        Sources documentaires consultées
                    </label>
                    <label class="flex items-center gap-2 text-sm font-semibold">
                        <input type="checkbox" name="require_bibliography" value="1" class="es-checkbox" @checked(old('require_bibliography', $project->require_bibliography ?? true))>
                        Bibliographie
                    </label>
                </div>

                <x-button type="submit">{{ $isNew ? 'Continuer → Consignes' : 'Enregistrer et continuer' }}</x-button>
            </form>
        </div>
    @endif

    @if ($step === 2)
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="es-wizard-panel">
                <div class="es-wizard-panel-head">
                    <span class="es-wizard-panel-num">2</span>
                    <div>
                        <h2 class="text-2xl font-black text-es-ink">Consignes</h2>
                        <p class="text-es-muted mt-1">Explique le travail à réaliser, les critères et la structure attendue.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="space-y-5 mt-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $project->title }}">
                    <input type="hidden" name="subject_id" value="{{ $project->subject_id }}">
                    <input type="hidden" name="skill_id" value="{{ $project->skill_id }}">
                    <input type="hidden" name="project_type" value="{{ $project->project_type }}">
                    <input type="hidden" name="submission_format" value="{{ $project->submission_format }}">
                    <input type="hidden" name="due_at" value="{{ $project->due_at?->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="require_sources" value="{{ $project->require_sources ? '1' : '0' }}">
                    <input type="hidden" name="require_bibliography" value="{{ $project->require_bibliography ? '1' : '0' }}">
                    <input type="hidden" name="next_step" value="3">

                    <div>
                        <label for="instructions" class="es-label">Consignes détaillées</label>
                        <textarea id="instructions" name="instructions" rows="14" class="es-textarea" required
                            placeholder="Contexte, objectifs, plan attendu, critères d'évaluation…">{{ old('instructions', $project->instructions) }}</textarea>
                        @error('instructions')<p class="es-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex gap-3">
                        <x-button type="submit">Continuer → Publication</x-button>
                        <x-button href="{{ route('admin.projects.build', ['project' => $project, 'step' => 1]) }}" variant="secondary">← Retour</x-button>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="es-card p-5">
                    <h3 class="font-extrabold mb-3">Pièces jointes professeur</h3>
                    <p class="text-sm text-es-muted mb-4">PDF, documents ou images à disposition des élèves.</p>

                    <form method="POST" action="{{ route('admin.projects.attachments.store', $project) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" class="es-input text-sm">
                        <x-button type="submit" variant="secondary" class="w-full es-btn-sm">Ajouter des fichiers</x-button>
                    </form>

                    @if ($project->attachments->isNotEmpty())
                        <ul class="mt-4 space-y-2">
                            @foreach ($project->attachments as $attachment)
                                <li class="flex items-center justify-between gap-2 text-sm rounded-xl bg-stone-50 px-3 py-2">
                                    <a href="{{ route('project-media.show', [$project, $attachment]) }}" class="es-link font-bold truncate" target="_blank">
                                        📎 {{ $attachment->displayName() }}
                                    </a>
                                    <form method="POST" action="{{ route('admin.projects.attachments.destroy', [$project, $attachment]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600">Suppr.</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>
        </div>
    @endif

    @if ($step === 3)
        <div class="es-wizard-panel max-w-3xl">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">3</span>
                <div>
                    <h2 class="text-2xl font-black text-es-ink">Publication</h2>
                    <p class="text-es-muted mt-1">Vérifie le résumé et assigne le projet aux élèves.</p>
                </div>
            </div>

            <div class="es-card p-5 mt-6 space-y-2 text-sm">
                <p><strong>Titre :</strong> {{ $project->title }}</p>
                <p><strong>Type :</strong> {{ $project->typeLabel() }}</p>
                <p><strong>Rendu :</strong> {{ $project->formatLabel() }}</p>
                <p><strong>Pièces jointes :</strong> {{ $project->attachments->count() }}</p>
                @if ($project->due_at)
                    <p><strong>Échéance :</strong> {{ $project->due_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.projects.publish', $project) }}" class="mt-8 space-y-6">
                @csrf
                @include('admin.activities.partials.publish-audience', [
                    'students' => $students,
                    'levels' => $levels,
                    'classGroups' => $classGroups,
                    'selectedIds' => $selectedStudentIds,
                ])
                <div class="flex flex-wrap gap-3">
                    <x-button type="submit">Publier le projet</x-button>
                    <x-button href="{{ route('admin.projects.build', ['project' => $project, 'step' => 2]) }}" variant="secondary">← Retour</x-button>
                </div>
            </form>
            @if ($project->isPublished())
                <form method="POST" action="{{ route('admin.projects.unpublish', $project) }}" class="mt-4">
                    @csrf
                    <x-button variant="secondary" type="submit">Remettre en brouillon</x-button>
                </form>
            @endif
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.ap-type-menu-item input[type=radio]').forEach(radio => {
    radio.addEventListener('change', () => {
        const group = radio.closest('.grid');
        group?.querySelectorAll('.ap-type-menu-item').forEach(el => el.classList.remove('ap-type-menu-item-active'));
        radio.closest('.ap-type-menu-item')?.classList.add('ap-type-menu-item-active');
    });
});
</script>
@endsection
