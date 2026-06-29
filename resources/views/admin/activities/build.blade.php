@extends('layouts.admin')

@php $isNew = ! $activity->exists; @endphp

@section('admin-content')
<div class="es-page-enter es-wizard-page">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.activities.index') }}" class="es-link text-sm font-bold">← Mes activités</a>
            <h1 class="es-page-title mt-3">{{ $isNew ? 'Créer une activité' : 'Modifier l\'activité' }}</h1>
            @unless ($isNew)
                <p class="es-page-subtitle">{{ $activity->title }}</p>
            @endunless
        </div>
        @unless ($isNew)
            <div class="flex flex-wrap gap-2">
                <x-button href="{{ route('admin.activities.preview', $activity) }}" variant="secondary" class="es-btn-sm">Aperçu élève</x-button>
                <x-button href="{{ route('admin.activities.submissions', $activity) }}" variant="secondary" class="es-btn-sm">Copies soumises</x-button>
            </div>
        @endunless
    </div>

    <x-activity-wizard-nav :step="$step" :activity="$activity"/>

    @if ($errors->any())
        <x-alert type="error" class="mb-6" title="Corrige ces points :">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- ═══ ÉTAPE 1 — Informations ═══ --}}
    @if ($step === 1)
        @php
            $defaultSubjectId = old('subject_id', $activity->subject_id ?? $subjects->first()?->id);
            $defaultSkillId = old('skill_id', $activity->skill_id);
        @endphp
        <div class="es-wizard-panel max-w-2xl">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">1</span>
                <div>
                    <h2 class="text-2xl font-black text-es-ink">Informations de base</h2>
                    <p class="text-es-muted mt-1">Donne un titre et lie l'activité à une matière et une compétence.</p>
                </div>
            </div>

            <form method="POST" action="{{ $isNew ? route('admin.activities.store') : route('admin.activities.update', $activity) }}" class="space-y-5 mt-8" id="activity-info-form">
                @csrf
                @unless ($isNew) @method('PUT') @endunless

                <x-input label="Titre de l'activité" name="title" value="{{ old('title', $activity->title) }}" required
                    placeholder="Ex. Comprendre un conte" :error="$errors->first('title')"/>

                <div>
                    <label for="description" class="es-label">Description pour les élèves</label>
                    <textarea id="description" name="description" class="es-textarea" rows="3"
                        placeholder="En une phrase, de quoi parle cette activité ?">{{ old('description', $activity->description) }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="subject_id" class="es-label">Matière</label>
                        <select id="subject_id" name="subject_id" class="es-select @error('subject_id') es-input-error @enderror" required>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) $defaultSubjectId === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<p class="es-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="skill_id" class="es-label">Compétence</label>
                        <select id="skill_id" name="skill_id" class="es-select @error('skill_id') es-input-error @enderror" required>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" data-subject="{{ $skill->subject_id }}"
                                    @selected((string) $defaultSkillId === (string) $skill->id)>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                        @error('skill_id')<p class="es-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="lesson_id" class="es-label">Leçon associée (optionnel)</label>
                    <select id="lesson_id" name="lesson_id" class="es-select">
                        <option value="">— Aucune —</option>
                        @foreach ($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->id }}" data-subject="{{ $lesson->subject_id }}"
                                @selected(old('lesson_id', $activity->lesson_id) == $lesson->id)>{{ $lesson->title }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-es-muted mt-1">L'élève pourra consulter cette leçon pendant l'activité.</p>
                </div>

                @php
                    $isHomework = (bool) old('is_homework', $activity->is_homework ?? false);
                    $dueValue = old('due_at', $activity->due_at?->format('Y-m-d\TH:i'));
                    $homeworkSlot = old('homework_slot', $activity->homework_slot);
                @endphp
                <div
                    class="rounded-2xl border border-stone-200 bg-stone-50/80 p-5 space-y-4"
                    x-data="{ isHomework: @json($isHomework) }"
                >
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_homework"
                            value="1"
                            class="mt-1 rounded border-stone-300 text-es-primary focus:ring-es-primary"
                            x-model="isHomework"
                            @checked($isHomework)
                        >
                        <span>
                            <span class="font-extrabold text-es-ink block">C'est un devoir</span>
                            <span class="text-sm text-es-muted">Les devoirs apparaissent dans une section dédiée pour les élèves, séparée des activités en classe.</span>
                        </span>
                    </label>

                    <div x-show="isHomework" x-cloak class="space-y-4 pt-2 border-t border-stone-200">
                        <div>
                            <label for="due_at" class="es-label">Date limite</label>
                            <input
                                type="datetime-local"
                                id="due_at"
                                name="due_at"
                                value="{{ $dueValue }}"
                                class="es-input @error('due_at') es-input-error @enderror"
                            >
                            @error('due_at')<p class="es-field-error">{{ $message }}</p>@enderror
                        </div>

                        <fieldset>
                            <legend class="es-label mb-3">Quand le devoir doit-il être fait ?</legend>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach (config('activity.homework_slots') as $slotKey => $slotLabel)
                                    <label @class([
                                        'es-homework-slot-option',
                                        'es-homework-slot-option-active' => $homeworkSlot === $slotKey,
                                    ])>
                                        <input
                                            type="radio"
                                            name="homework_slot"
                                            value="{{ $slotKey }}"
                                            class="sr-only"
                                            @checked($homeworkSlot === $slotKey)
                                        >
                                        <span class="text-2xl mb-2 block" aria-hidden="true">{{ $slotKey === 'during_school' ? '🏫' : '🏠' }}</span>
                                        <span class="font-extrabold text-es-ink">{{ $slotLabel }}</span>
                                        <span class="text-xs text-es-muted mt-1 block">
                                            {{ $slotKey === 'during_school' ? 'À faire en classe ou pendant les heures scolaires.' : 'À faire à la maison après les cours.' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('homework_slot')<p class="es-field-error mt-2">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-4">
                    <x-button type="submit">{{ $isNew ? 'Continuer → Contenu' : 'Enregistrer et continuer' }}</x-button>
                </div>
            </form>
        </div>

        @push('scripts')
        <script>
            (function () {
                function filterSkills() {
                    const subjectSelect = document.getElementById('subject_id');
                    const skillSelect = document.getElementById('skill_id');
                    if (!subjectSelect || !skillSelect) return;

                    const subjectId = String(subjectSelect.value);
                    let firstMatch = null;
                    let currentValid = false;

                    skillSelect.querySelectorAll('option').forEach(function (opt) {
                        const match = String(opt.dataset.subject) === subjectId;
                        opt.hidden = !match;
                        opt.disabled = !match;
                        if (match) {
                            if (!firstMatch) firstMatch = opt;
                            if (opt.selected) currentValid = true;
                        }
                    });

                    if (!currentValid && firstMatch) {
                        skillSelect.value = firstMatch.value;
                    }
                }

                function filterLessons() {
                    const subjectSelect = document.getElementById('subject_id');
                    const lessonSelect = document.getElementById('lesson_id');
                    if (!subjectSelect || !lessonSelect) return;

                    const subjectId = String(subjectSelect.value);
                    lessonSelect.querySelectorAll('option').forEach(function (opt, i) {
                        if (i === 0) return;
                        const match = String(opt.dataset.subject) === subjectId;
                        opt.hidden = !match;
                        opt.disabled = !match;
                        if (!match && opt.selected) lessonSelect.value = '';
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const subjectSelect = document.getElementById('subject_id');
                    if (subjectSelect) {
                        subjectSelect.addEventListener('change', function () {
                            filterSkills();
                            filterLessons();
                        });
                        filterSkills();
                        filterLessons();
                    }
                });
            })();
        </script>
        @endpush
    @endif

    {{-- ═══ ÉTAPE 2 — Contenu ═══ --}}
    @if ($step === 2)
        <div x-data="{ pageType: 'interactive', openPage: null }" class="grid gap-8 xl:grid-cols-5">
            {{-- Colonne gauche : étapes ajoutées --}}
            <div class="xl:col-span-2 space-y-4">
                <h2 class="text-lg font-black text-es-ink">Étapes de l'activité</h2>

                @forelse ($activity->pages as $page)
                    @php
                        $meta = config('activity.page_types.'.$page->type, []);
                        $qCount = $page->questions->count();
                    @endphp
                    <article class="es-step-card" style="--step-accent: {{ $meta['color'] ?? '#4f46e5' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex gap-3 min-w-0">
                                <span class="es-step-card-icon">{{ $meta['icon'] ?? '📋' }}</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide opacity-70">Étape {{ $page->page_order }}</p>
                                    <h3 class="font-extrabold text-es-ink truncate">{{ $page->title }}</h3>
                                    <p class="text-sm text-es-muted mt-1">{{ $meta['label'] ?? $page->type }}
                                        @if ($page->isInteractive()) · {{ $qCount }} question(s) @endif
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.activities.pages.destroy', [$activity, $page]) }}"
                                onsubmit="return confirm('Supprimer cette étape ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 font-bold p-1 hover:bg-red-50 rounded-lg" aria-label="Supprimer">×</button>
                            </form>
                        </div>
                        @if ($page->isInteractive())
                            <button type="button" @click="openPage = openPage === {{ $page->id }} ? null : {{ $page->id }}"
                                class="mt-3 text-sm font-bold text-es-primary hover:underline">
                                <span x-text="openPage === {{ $page->id }} ? 'Masquer les questions' : '+ Ajouter une question'"></span>
                            </button>
                        @endif
                    </article>
                @empty
                    <div class="es-empty es-step-empty">
                        <p class="text-4xl mb-2">📋</p>
                        <p class="font-extrabold">Aucune étape</p>
                        <p class="text-sm text-es-muted mt-1">Choisis un format à droite pour commencer.</p>
                    </div>
                @endforelse

                {{-- Panneaux questions (sous les cartes) --}}
                @foreach ($activity->pages as $page)
                    @if ($page->isInteractive())
                        <div x-show="openPage === {{ $page->id }}" class="es-wizard-panel !p-5" style="display: none;">
                            <h3 class="font-extrabold mb-3">Questions — {{ $page->title }}</h3>
                            @foreach ($page->questions as $question)
                                @php $qMeta = config('activity.question_types.'.$question->type, []); @endphp
                                <div class="flex items-start justify-between gap-2 py-2 border-b border-stone-100 last:border-0">
                                    <div>
                                        <span class="text-xs font-bold text-es-primary">{{ $qMeta['icon'] ?? '' }} {{ $qMeta['label'] ?? $question->type }}</span>
                                        <p class="text-sm font-semibold">{{ $question->prompt }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.activities.questions.destroy', [$activity, $question]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 font-bold">×</button>
                                    </form>
                                </div>
                            @endforeach
                            @include('admin.activities.partials.question-form', [
                                'activity' => $activity,
                                'page' => $page,
                                'questionTypes' => $questionTypes,
                            ])
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Colonne droite : ajouter une étape --}}
            <div class="xl:col-span-3">
                <div class="es-wizard-panel">
                    <div class="es-wizard-panel-head">
                        <span class="es-wizard-panel-num">2</span>
                        <div>
                            <h2 class="text-2xl font-black text-es-ink">Ajouter une étape</h2>
                            <p class="text-es-muted mt-1">Tout se crée ici, sur le site — pas de module à importer.</p>
                        </div>
                    </div>

                    <p class="text-sm font-bold text-es-muted mt-8 mb-4">Choisis le format :</p>
                    @if ($subjectWorkspace ?? null)
                        <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4 text-sm text-indigo-900 mb-4">
                            <strong>{{ $activity->subject->name }}</strong> — {{ $subjectWorkspace['hint'] }}
                        </div>
                    @endif
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 mb-6">
                        @foreach ($pageTypes as $typeKey => $typeMeta)
                            <button type="button"
                                @click="pageType = '{{ $typeKey }}'; document.getElementById('page-type-input') && (document.getElementById('page-type-input').value = '{{ $typeKey }}')"
                                class="es-type-picker {{ ! empty($typeMeta['featured']) ? 'ring-2 ring-es-primary/20' : '' }}"
                                :class="pageType === '{{ $typeKey }}' ? 'es-type-picker-active' : ''"
                                style="--type-color: {{ $typeMeta['color'] ?? '#4f46e5' }}">
                                @if (! empty($typeMeta['featured']))
                                    <span class="text-[10px] font-bold uppercase text-es-primary">Recommandé</span>
                                @endif
                                <span class="es-type-picker-icon">{{ $typeMeta['icon'] }}</span>
                                <span class="es-type-picker-label">{{ $typeMeta['label'] }}</span>
                                <span class="es-type-picker-desc text-xs">{{ $typeMeta['description'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.activities.pages.store', $activity) }}" enctype="multipart/form-data"
                        class="space-y-5 border-t border-stone-100 pt-6"
                        @submit="document.getElementById('page-type-input').value = pageType">
                        @csrf
                        <input type="hidden" name="type" id="page-type-input" value="interactive">

                        <x-input label="Titre de cette étape" name="title" required placeholder="Ex. Exercice 1"
                            value="{{ old('title') }}" :error="$errors->first('title')"/>

                        <div>
                            <label class="es-label">Consignes pour l'élève</label>
                            <textarea name="body" class="es-textarea" rows="3" placeholder="Explique ce que l'élève doit faire…">{{ old('body') }}</textarea>
                        </div>

                        <div x-show="pageType === 'pdf_worksheet'" class="es-upload-zone" style="display: none;">
                            <label class="es-label">Ton fichier PDF</label>
                            <input type="file" name="pdf" accept="application/pdf" class="es-input !py-4">
                            @error('pdf')<p class="es-field-error mt-2">{{ $message }}</p>@enderror
                            <p class="text-xs text-es-muted mt-2">L'élève écrira et dessinera par-dessus. Tu corrigeras à l'encre rouge après soumission.</p>
                        </div>

                        <div x-show="pageType === 'free_write'" class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 text-sm text-emerald-900" style="display: none;">
                            L'élève aura une zone blanche pour <strong>écrire</strong>, <strong>dessiner</strong> et <strong>surligner</strong> — directement sur le site.
                        </div>

                        <div x-show="pageType === 'interactive'" class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4 text-sm text-indigo-900">
                            Après avoir ajouté l'étape, tu pourras créer des questions parmi <strong>10 formats</strong> (QCM, vrai/faux, texte à trous, etc.).
                        </div>

                        <div x-show="pageType === 'reading_comprehension' || pageType === 'recitation'" x-cloak class="space-y-3" style="display: none;">
                            <div>
                                <label class="es-label">Texte à lire</label>
                                <textarea name="passage" class="es-textarea" rows="8" placeholder="Colle le texte de compréhension ou de récitation…"></textarea>
                            </div>
                            <div>
                                <label class="es-label">Fichier audio (lecture / écoute)</label>
                                <input type="file" name="audio" accept="audio/*" class="es-input">
                                <p class="text-xs text-es-muted mt-1">L'élève pourra écouter et masquer/réafficher le texte.</p>
                            </div>
                        </div>

                        <div x-show="pageType === 'oral_recording'" x-cloak class="rounded-2xl bg-pink-50 border border-pink-100 p-4 text-sm text-pink-900" style="display: none;">
                            L'élève enregistrera <strong>audio ou vidéo</strong> directement sur la tablette (oral, natation, etc.).
                        </div>

                        <div x-show="pageType === 'rich_document'" x-cloak class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-900" style="display: none;">
                            Document d'écriture : l'élève bascule entre <strong>texte riche</strong> (comme un doc) et <strong>dessin</strong>.
                        </div>

                        <div x-show="pageType === 'math_scroll'" x-cloak class="rounded-2xl bg-violet-50 border border-violet-100 p-4 text-sm text-violet-900" style="display: none;">
                            Grande feuille blanche <strong>défilante</strong> — idéale pour les exercices de maths.
                        </div>

                        @error('type')<p class="es-field-error">{{ $message }}</p>@enderror

                        <x-button type="submit">+ Ajouter l'étape</x-button>
                    </form>
                </div>

                @if ($activity->pages->isNotEmpty())
                    <div class="mt-6 flex justify-end">
                        <x-button href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 3]) }}">
                            Continuer → Publication
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ═══ ÉTAPE 3 — Publication ═══ --}}
    @if ($step === 3)
        <div class="max-w-3xl mx-auto">
            <div class="es-wizard-panel">
                <div class="es-wizard-panel-head">
                    <span class="es-wizard-panel-num">3</span>
                    <div>
                        <h2 class="text-2xl font-black text-es-ink">Vérifier et publier</h2>
                        <p class="text-es-muted mt-1">Relis le résumé, choisis les élèves destinataires, puis publie.</p>
                    </div>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="rounded-2xl bg-stone-50 p-5">
                        <h3 class="font-extrabold text-lg">{{ $activity->title }}</h3>
                        <p class="text-es-muted mt-1">{{ $activity->subject->name }} · {{ $activity->skill->name }}</p>
                        @if ($activity->description)
                            <p class="mt-3 text-sm">{{ $activity->description }}</p>
                        @endif
                        @if ($activity->isHomework())
                            <div class="mt-4 inline-flex flex-wrap items-center gap-2 rounded-xl bg-amber-100 px-3 py-2 text-sm font-bold text-amber-900">
                                <span>📋 Devoir</span>
                                <span>· {{ $activity->homeworkSlotLabel() }}</span>
                                @if ($activity->due_at)
                                    <span>· Pour le {{ $activity->due_at->translatedFormat('d M Y · H:i') }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <h3 class="font-bold text-es-muted text-sm uppercase tracking-wide">{{ $activity->pages->count() }} étape(s)</h3>
                    @foreach ($activity->pages as $page)
                        @php $meta = config('activity.page_types.'.$page->type, []); @endphp
                        <div class="flex items-center gap-4 rounded-2xl border border-stone-200 p-4">
                            <span class="text-2xl">{{ $meta['icon'] ?? '📋' }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold">{{ $page->page_order }}. {{ $page->title }}</p>
                                <p class="text-sm text-es-muted">{{ $meta['label'] ?? $page->type }}
                                    @if ($page->isInteractive()) — {{ $page->questions->count() }} question(s) @endif
                                    @if ($page->isPdfWorksheet() && $page->mediaFile) — {{ $page->mediaFile->filename }} @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.activities.publish', $activity) }}" id="activity-publish-form">
                    @csrf

                    @include('admin.activities.partials.publish-audience', [
                        'students' => $students,
                        'levels' => $levels,
                        'classGroups' => $classGroups,
                        'selectedIds' => $assignedStudentIds ?? [],
                    ])
                </form>

                <div class="flex flex-wrap gap-3 mt-10 pt-6 border-t border-stone-100">
                    <x-button href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 2]) }}" variant="secondary">← Modifier le contenu</x-button>
                    <x-button href="{{ route('admin.activities.preview', $activity) }}" variant="secondary">Aperçu élève</x-button>
                    @if ($activity->status !== 'published')
                        <x-button type="submit" form="activity-publish-form" :disabled="$students->isEmpty()">🚀 Publier l'activité</x-button>
                    @else
                        <span class="inline-flex items-center rounded-xl bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-800">
                            ✓ Publiée · {{ count($assignedStudentIds ?? []) }} élève(s)
                        </span>
                        <x-button type="submit" form="activity-publish-form" variant="secondary" :disabled="$students->isEmpty()">Mettre à jour les destinataires</x-button>
                        <form method="POST" action="{{ route('admin.activities.unpublish', $activity) }}">@csrf<x-button type="submit" variant="secondary">Dépublier</x-button></form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
