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

    @if ($errors->any())
        <x-alert type="error" class="mb-6" title="Impossible d'enregistrer — corrige ces points :">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @if ($step === 1)
        @php
            $defaultSubjectId = old('subject_id', $project->subject_id ?? $subjects->first()?->id);
            $defaultPeriodId = old('report_period_id', $project->report_period_id ?? $periods->first()?->id);
        @endphp
        <div class="es-wizard-panel max-w-2xl" x-data="{
            subjectId: '{{ $defaultSubjectId }}',
            selectedSkills: @js(array_map('strval', old('skill_ids', $selectedSkillIds ?? []))),
            toggleSkill(id) {
                id = String(id);
                if (this.selectedSkills.includes(id)) {
                    this.selectedSkills = this.selectedSkills.filter(s => s !== id);
                } else {
                    this.selectedSkills.push(id);
                }
            },
            isSelected(id) { return this.selectedSkills.includes(String(id)); },
            resetSkillsForSubject() {
                this.selectedSkills = [];
                this.$root.querySelectorAll('input[name=\'skill_ids[]\']').forEach(cb => { cb.checked = false; });
            }
        }">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">1</span>
                <div>
                    <h2 class="text-2xl font-black text-es-ink">Informations & bulletin</h2>
                    <p class="text-es-muted mt-1">Titre, matière, poids dans le bulletin et compétences évaluées.</p>
                </div>
            </div>

            <form method="POST" action="{{ $isNew ? route('admin.projects.store') : route('admin.projects.update', $project) }}" class="space-y-6 mt-8">
                @csrf
                @unless ($isNew) @method('PUT') @endunless

                <x-input label="Titre du projet" name="title" value="{{ old('title', $project->title) }}" required :error="$errors->first('title')"/>

                <div>
                    <label for="subject_id" class="es-label">Matière</label>
                    <select id="subject_id" name="subject_id" class="es-select @error('subject_id') es-input-error @enderror" required x-model="subjectId" @change="resetSkillsForSubject()">
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($defaultSubjectId == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="report_period_id" class="es-label">Période bulletin</label>
                        @if ($periods->isEmpty())
                            <p class="text-sm text-amber-700 font-semibold rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                Aucune période bulletin n'est configurée. Contacte l'administrateur ou crée une période avant de publier un projet noté.
                            </p>
                        @else
                            <select id="report_period_id" name="report_period_id" class="es-select @error('report_period_id') es-input-error @enderror" required>
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}" @selected($defaultPeriodId == $period->id)>
                                        {{ $period->label }} ({{ $period->school_year }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('report_period_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-input label="Poids dans le bulletin (%)" name="weight_percent" type="number" step="0.5" min="0" max="100"
                            value="{{ old('weight_percent', $project->weight_percent ?? 0) }}" required :error="$errors->first('weight_percent')"/>
                        <p class="text-xs text-es-muted mt-1">Comme un examen : la somme examens + projets = 100 % par matière.</p>
                    </div>
                </div>

                <div>
                    <label class="es-label">Compétences évaluées <span class="text-red-600">*</span></label>
                    <p class="text-xs text-es-muted mb-3">Sélectionne au moins une compétence. Si plusieurs, répartis le poids entre elles (total 100 %).</p>
                    @error('skill_ids')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
                    @error('skill_weights')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror

                    <div @class([
                        'space-y-2 rounded-2xl border bg-stone-50/80 p-4',
                        'border-red-300' => $errors->has('skill_ids'),
                        'border-stone-200' => ! $errors->has('skill_ids'),
                    ])>
                        @foreach ($skills as $skill)
                            @php
                                $oldWeight = old('skill_weights.'.$skill->id, $project->skills->firstWhere('id', $skill->id)?->pivot?->weight_percent);
                            @endphp
                            <div class="flex flex-wrap items-center gap-3 py-1" x-show="subjectId == '{{ $skill->subject_id }}'" x-cloak>
                                <label class="flex items-center gap-2 text-sm font-semibold min-w-[12rem]">
                                    <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="es-checkbox"
                                        @checked(in_array($skill->id, old('skill_ids', $selectedSkillIds ?? [])))
                                        @change="toggleSkill({{ $skill->id }})">
                                    {{ $skill->name }}
                                </label>
                                <div class="flex items-center gap-2" x-show="isSelected('{{ $skill->id }}') && selectedSkills.length > 1" x-cloak>
                                    <input type="number" name="skill_weights[{{ $skill->id }}]" class="es-input w-24 es-input-sm"
                                        step="0.5" min="0.01" max="100" placeholder="Part %"
                                        value="{{ $oldWeight }}">
                                    <span class="text-xs text-es-muted">% de la note du projet</span>
                                </div>
                            </div>
                        @endforeach
                        @if ($skills->isEmpty())
                            <p class="text-sm text-es-muted">Aucune compétence disponible pour cette matière.</p>
                        @elseif ($skills->where('subject_id', $defaultSubjectId)->isEmpty())
                            <p class="text-sm text-es-muted" x-show="subjectId == '{{ $defaultSubjectId }}'">Aucune compétence pour cette matière.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="es-label">Type de projet</label>
                    @error('project_type')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
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
                    @error('submission_format')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
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

                @if ($periods->isEmpty())
                    <x-button type="submit" disabled>{{ $isNew ? 'Continuer → Consignes' : 'Enregistrer et continuer' }}</x-button>
                @else
                    <x-button type="submit">{{ $isNew ? 'Continuer → Consignes' : 'Enregistrer et continuer' }}</x-button>
                @endif
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
                    <input type="hidden" name="report_period_id" value="{{ $project->report_period_id }}">
                    <input type="hidden" name="weight_percent" value="{{ $project->weight_percent }}">
                    @foreach ($project->skills as $skill)
                        <input type="hidden" name="skill_ids[]" value="{{ $skill->id }}">
                        <input type="hidden" name="skill_weights[{{ $skill->id }}]" value="{{ $skill->pivot->weight_percent }}">
                    @endforeach
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
                <p><strong>Bulletin :</strong>
                    @if ($project->reportPeriod)
                        {{ $project->reportPeriod->label }} · {{ number_format($project->weight_percent, 0) }}%
                    @else
                        —
                    @endif
                </p>
                @if ($project->skills->isNotEmpty())
                    <p><strong>Compétences :</strong>
                        {{ $project->skills->map(fn ($s) => $s->name.($project->skills->count() > 1 ? ' ('.number_format($s->pivot->weight_percent, 0).'%)' : ''))->join(', ') }}
                    </p>
                @endif
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
