@extends('layouts.student')

@section('student-content')
@php
    $bibliographyGuide = config('bibliography_guide');
    $bibliography = old('bibliography', $submission->bibliography ?? []);
    $defaultBiblioEntry = [
        'style' => $bibliographyGuide['default_style'] ?? 'dionne',
        'document_type' => 'books',
        'document_case' => 'book_whole',
        'title' => '',
        'author' => '',
        'year' => '',
        'publisher' => '',
        'url' => '',
        'notes' => '',
    ];
    if (empty($bibliography)) {
        $bibliography = [$defaultBiblioEntry];
    }
@endphp

<div
    class="es-project-work es-page-enter pb-28"
    x-data="projectWorkspace({
        saveUrl: @js(route('student.projects.save', $project)),
        uploadUrl: @js(route('student.projects.upload', $project)),
        deleteFileUrl: @js(url('/student/projects/'.$project->id.'/files')),
        submitUrl: @js(route('student.projects.submit', $project)),
        csrf: @js(csrf_token()),
        canEdit: @json($canEdit),
        content: @js($submission->content ?? ''),
        researchNotes: @js($submission->research_notes ?? ''),
        bibliography: @js($bibliography),
        files: @js($submission->files->map(fn ($f) => ['id' => $f->id, 'name' => $f->displayName(), 'url' => route('project-submission-files.show', [$project, $f])])->values()),
        allowsWrite: @json($project->allowsOnlineWrite()),
        allowsUpload: @json($project->allowsUpload()),
        requireBibliography: @json($project->require_bibliography),
        bibliographyGuide: @js($bibliographyGuide),
    })"
>
    <div class="es-container py-5 max-w-5xl">
        <a href="{{ route('student.projects.index') }}" class="es-link text-sm font-bold">← Mes projets</a>

        <header class="mt-3 mb-5">
            <p class="text-xs font-bold uppercase tracking-wider text-es-muted">{{ $project->subject->name }}</p>
            <h1 class="text-xl md:text-2xl font-black text-es-ink leading-tight break-words">{{ $project->title }}</h1>
            <p class="text-sm text-es-muted mt-1" x-text="`Étape ${stepIndex + 1} / ${steps.length} · ${currentStep?.label}`"></p>
            <p class="text-xs font-semibold mt-1" :class="saveState === 'saved' ? 'text-emerald-600' : saveState === 'error' ? 'text-red-600' : 'text-es-muted'" x-text="saveLabel"></p>

            @if ($submission->workflow_status === 'returned' && $submission->correction?->comment)
                <x-alert type="warning" class="mt-3" title="Renvoyé par le professeur">{{ $submission->correction->comment }}</x-alert>
            @elseif ($submission->workflow_status === 'submitted')
                <x-alert type="info" class="mt-3" title="Projet soumis">En attente de correction.</x-alert>
            @endif
        </header>

        {{-- Progression --}}
        <div class="mb-4">
            <div class="h-1.5 w-full rounded-full bg-stone-200 overflow-hidden">
                <div class="h-full rounded-full bg-es-primary transition-all duration-300" :style="`width: ${progressPercent}%`"></div>
            </div>
        </div>

        <nav class="es-project-steps mb-6" aria-label="Étapes du projet">
            <ol class="flex gap-1.5 overflow-x-auto pb-1 -mx-1 px-1">
                <template x-for="(step, index) in steps" :key="step.id">
                    <li class="shrink-0">
                        <button
                            type="button"
                            class="es-project-step-pill"
                            :class="{
                                'es-project-step-pill-active': index === stepIndex,
                                'es-project-step-pill-done': index < stepIndex,
                            }"
                            @click="goToStep(index)"
                        >
                            <span class="es-project-step-icon" x-text="step.icon"></span>
                            <span class="es-project-step-num" x-text="index + 1"></span>
                            <span class="es-project-step-label" x-text="step.label"></span>
                        </button>
                    </li>
                </template>
            </ol>
        </nav>

        {{-- Consignes --}}
        <section x-show="isStep('brief')" x-cloak class="space-y-4">
            <div class="es-card es-project-step-card p-5">
                <h2 class="font-extrabold text-lg mb-2">📋 Consignes</h2>
                <p class="text-sm text-es-muted mb-4">Lis bien ce que ton professeur attend avant de commencer.</p>
                <div class="es-project-prose">{{ $project->instructions }}</div>
            </div>
            @if ($project->attachments->isNotEmpty())
                <div class="es-card es-project-step-card p-5">
                    <h3 class="font-extrabold mb-3">Documents fournis</h3>
                    <ul class="space-y-2">
                        @foreach ($project->attachments as $attachment)
                            <li>
                                <a href="{{ route('project-media.show', [$project, $attachment]) }}" class="es-link font-bold text-base break-all" target="_blank">
                                    📎 {{ $attachment->displayName() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- Recherche --}}
        <section x-show="isStep('research')" x-cloak>
            <div class="es-card es-project-step-card p-5">
                <h2 class="font-extrabold text-lg mb-2">🔍 Recherche</h2>
                <p class="text-sm text-es-muted mb-4">Note tes idées, faits et infos importantes en points (puces). Utilise des tirets « - » au début de chaque ligne.</p>
                <textarea
                    x-model="researchNotes"
                    @input.debounce.800ms="save()"
                    :disabled="!canEdit"
                    rows="14"
                    class="es-textarea w-full text-base leading-relaxed"
                    placeholder="- Mon sujet principal est…&#10;- J'ai trouvé que…&#10;- Point important : …"
                ></textarea>
            </div>
        </section>

        {{-- Rédaction (sur le site, séparée du produit final) --}}
        <section x-show="isStep('write')" x-cloak>
            <div class="es-card es-project-step-card p-5">
                <h2 class="font-extrabold text-lg mb-2">✍️ Rédaction</h2>
                <p class="text-sm text-es-muted mb-4">Rédige ton travail directement ici. Cette étape est distincte du dépôt du produit final.</p>
                <textarea
                    x-model="content"
                    @input.debounce.800ms="save()"
                    :disabled="!canEdit"
                    rows="16"
                    class="es-textarea w-full text-base leading-relaxed"
                    placeholder="Introduction…&#10;&#10;Développement…&#10;&#10;Conclusion…"
                ></textarea>
            </div>
        </section>

        {{-- Produit final (fichier à télécharger / déposer) --}}
        <section x-show="isStep('final')" x-cloak>
            <div class="es-card es-project-step-card p-5 space-y-4">
                <div>
                    <h2 class="font-extrabold text-lg mb-2">📄 Produit final</h2>
                    <p class="text-sm text-es-muted">Dépose ici ton travail terminé (PDF, Word ou PowerPoint, max 50 Mo). Tu peux aussi le télécharger une fois déposé.</p>
                </div>
                <template x-if="canEdit">
                    <div class="rounded-2xl border-2 border-dashed border-violet-200 bg-violet-50/40 p-8 text-center">
                        <input type="file" x-ref="fileInput" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx" @change="uploadFile($event)">
                        <p class="text-3xl mb-2">📄</p>
                        <button type="button" class="es-btn es-btn-primary w-full sm:w-auto" @click="$refs.fileInput.click()">Déposer le produit final</button>
                    </div>
                </template>
                <ul class="space-y-2" x-show="files.length > 0">
                    <template x-for="file in files" :key="file.id">
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-stone-50 border border-stone-200 px-4 py-3">
                            <a :href="file.url" class="es-link font-bold truncate text-base min-w-0" target="_blank" x-text="'📎 ' + file.name"></a>
                            <button type="button" x-show="canEdit" class="text-sm font-bold text-red-600 shrink-0" @click="removeFile(file.id)">Suppr.</button>
                        </li>
                    </template>
                </ul>
                <p class="text-xs text-es-muted" x-show="files.length === 0 && !canEdit">Aucun fichier déposé.</p>
            </div>
        </section>

        {{-- Bibliographie --}}
        <section x-show="isStep('biblio')" x-cloak class="space-y-4">
            <div class="es-card es-project-step-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-extrabold text-lg">📚 Bibliographie</h2>
                        <p class="text-sm text-es-muted">Choisis le style et le type de document, puis remplis ta référence. L'aide-mémoire à droite te montre comment la présenter.</p>
                    </div>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm shrink-0" x-show="canEdit" @click="addBiblio()">+ Référence</button>
                </div>

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,340px)] lg:items-start">
                    <div class="space-y-3 min-w-0">
                        <template x-for="(entry, index) in bibliography" :key="index">
                            <div
                                class="rounded-2xl border p-4 space-y-3 transition-colors"
                                :class="activeBiblioIndex === index ? 'border-es-primary bg-violet-50/30' : 'border-stone-200 bg-stone-50/50'"
                                @click="setActiveBiblio(index)"
                            >
                                <p class="text-xs font-bold text-es-muted" x-text="'Référence ' + (index + 1)"></p>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="es-label">Style bibliographique</label>
                                        <select
                                            x-model="entry.style"
                                            @change="onEntryStyleChange(entry)"
                                            @focus="setActiveBiblio(index)"
                                            :disabled="!canEdit"
                                            class="es-select w-full"
                                        >
                                            <template x-for="opt in styleOptions()" :key="opt.key">
                                                <option :value="opt.key" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="es-label">Type de document</label>
                                        <select
                                            x-model="entry.document_type"
                                            @change="onEntryDocChange(entry)"
                                            @focus="setActiveBiblio(index)"
                                            :disabled="!canEdit"
                                            class="es-select w-full"
                                        >
                                            <template x-for="opt in documentOptions()" :key="opt.key">
                                                <option :value="opt.key" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="es-label">Cas de figure</label>
                                    <select
                                        x-model="entry.document_case"
                                        @change="onEntryCaseChange(entry)"
                                        @focus="setActiveBiblio(index)"
                                        :disabled="!canEdit"
                                        class="es-select w-full"
                                    >
                                        <template x-for="opt in caseOptions(entry.document_type)" :key="opt.key">
                                            <option :value="opt.key" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="es-label">Titre *</label>
                                    <input type="text" x-model="entry.title" @input.debounce.800ms="save()" @focus="setActiveBiblio(index)" :disabled="!canEdit" class="es-input w-full">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="es-label">Auteur</label>
                                        <input type="text" x-model="entry.author" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input w-full">
                                    </div>
                                    <div>
                                        <label class="es-label">Année</label>
                                        <input type="text" x-model="entry.year" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input w-full">
                                    </div>
                                </div>
                                <div>
                                    <label class="es-label">Éditeur / revue / site</label>
                                    <input type="text" x-model="entry.publisher" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input w-full">
                                </div>
                                <div>
                                    <label class="es-label">URL (si source en ligne)</label>
                                    <input type="url" x-model="entry.url" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input w-full" placeholder="https://…">
                                </div>
                                <button type="button" x-show="canEdit && bibliography.length > 1" class="text-sm font-bold text-red-600" @click.stop="bibliography.splice(index, 1); save()">Supprimer</button>
                            </div>
                        </template>
                    </div>

                    <aside class="es-biblio-guide lg:sticky lg:top-4">
                        <div class="rounded-2xl border border-violet-200 bg-violet-50/50 p-4 space-y-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-es-primary">Aide-mémoire</p>
                                <h3 class="font-extrabold text-base mt-1">Comment citer ta source</h3>
                                <p class="text-xs text-es-muted mt-1">Comme le guide Collecto (BANQ) : choisis le style, le type et le cas pour voir le modèle.</p>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="es-label text-xs">1. Style bibliographique</label>
                                    <select x-model="bibGuideStyle" @change="onGuideChange()" class="es-select w-full text-sm">
                                        <template x-for="opt in styleOptions()" :key="opt.key">
                                            <option :value="opt.key" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                    <p class="text-[11px] text-es-muted mt-1" x-text="guide.styles?.[bibGuideStyle]?.description"></p>
                                </div>
                                <div>
                                    <label class="es-label text-xs">2. Type de document</label>
                                    <select x-model="bibGuideDoc" @change="bibGuideCase = firstCaseForDoc(bibGuideDoc); onGuideChange()" class="es-select w-full text-sm">
                                        <template x-for="opt in documentOptions()" :key="opt.key">
                                            <option :value="opt.key" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="es-label text-xs">3. Cas de figure</label>
                                    <select x-model="bibGuideCase" @change="onGuideChange()" class="es-select w-full text-sm">
                                        <template x-for="opt in caseOptions(bibGuideDoc)" :key="opt.key">
                                            <option :value="opt.key" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="rounded-xl bg-white border border-stone-200 p-3 space-y-3 text-sm min-w-0">
                                <p class="font-extrabold text-es-ink" x-text="bibGuideContent.title"></p>
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-es-muted mb-1">Structure</p>
                                    <p class="es-project-prose text-sm" x-text="bibGuideContent.structure"></p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-es-muted mb-1">Exemple</p>
                                    <p class="es-project-prose text-sm italic text-es-ink/90" x-text="bibGuideContent.example"></p>
                                </div>
                                <template x-if="(bibGuideContent.tips ?? []).length">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-es-muted mb-1">Conseils</p>
                                        <ul class="space-y-1 text-xs text-es-muted list-disc pl-4">
                                            <template x-for="(tip, tipIndex) in bibGuideContent.tips" :key="tipIndex">
                                                <li x-text="tip"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        {{-- Révision --}}
        <section x-show="isStep('review')" x-cloak class="space-y-4">
            <div class="es-card es-project-step-card p-5">
                <h2 class="font-extrabold text-lg mb-4">✅ Révision finale</h2>
                <p class="text-sm text-es-muted mb-5">Vérifie que tout est complet avant de soumettre.</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 py-2 border-b border-stone-100">
                        <dt class="font-bold text-es-muted">Recherche</dt>
                        <dd class="font-bold shrink-0" :class="researchNotes.trim() ? 'text-emerald-600' : 'text-amber-600'" x-text="researchNotes.trim() ? '✓ Rempli' : 'À compléter'"></dd>
                    </div>
                    @if ($project->allowsOnlineWrite())
                    <div class="flex justify-between gap-4 py-2 border-b border-stone-100">
                        <dt class="font-bold text-es-muted">Rédaction</dt>
                        <dd class="font-bold shrink-0" :class="hasWorkContent() ? 'text-emerald-600' : 'text-amber-600'" x-text="hasWorkContent() ? '✓ Rempli' : 'À compléter'"></dd>
                    </div>
                    @endif
                    @if ($project->allowsUpload())
                    <div class="flex justify-between gap-4 py-2 border-b border-stone-100">
                        <dt class="font-bold text-es-muted">Produit final</dt>
                        <dd class="font-bold shrink-0" :class="files.length ? 'text-emerald-600' : 'text-amber-600'" x-text="files.length ? files.length + ' fichier(s)' : 'À compléter'"></dd>
                    </div>
                    @endif
                    @if ($project->require_bibliography)
                    <div class="flex justify-between gap-4 py-2 border-b border-stone-100">
                        <dt class="font-bold text-es-muted">Bibliographie</dt>
                        <dd class="font-bold shrink-0" :class="filledBibliography().length ? 'text-emerald-600' : 'text-amber-600'" x-text="filledBibliography().length ? filledBibliography().length + ' réf.' : 'À compléter'"></dd>
                    </div>
                    @endif
                </dl>
            </div>
        </section>

        <footer class="es-project-nav fixed bottom-0 left-0 right-0 z-40 border-t border-stone-200 bg-white/95 backdrop-blur-md px-4 py-3 safe-area-pb">
            <div class="es-container max-w-5xl flex items-center gap-3">
                <button type="button" class="es-btn es-btn-secondary flex-1" @click="goPrev()" :disabled="isFirstStep">
                    ← Précédent
                </button>
                @if ($canEdit)
                    <template x-if="!isLastStep">
                        <button type="button" class="es-btn es-btn-primary flex-1" @click="goNext()">
                            Suivant →
                        </button>
                    </template>
                    <template x-if="isLastStep">
                        <button type="button" class="es-btn es-btn-primary flex-1" @click="submitProject()" :disabled="submitting">
                            <span x-show="!submitting">Soumettre ✓</span>
                            <span x-show="submitting">Envoi…</span>
                        </button>
                    </template>
                @else
                    <button type="button" class="es-btn es-btn-primary flex-1" @click="goNext()" :disabled="isLastStep">
                        Suivant →
                    </button>
                @endif
            </div>
        </footer>
    </div>
</div>
@endsection
