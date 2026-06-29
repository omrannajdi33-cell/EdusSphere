@extends('layouts.student')

@section('student-content')
@php
    $sources = old('sources', $submission->sources ?? []);
    $bibliography = old('bibliography', $submission->bibliography ?? []);
    if (empty($sources)) { $sources = [['type' => 'website', 'title' => '', 'author' => '', 'url' => '', 'notes' => '', 'accessed_at' => '']]; }
    if (empty($bibliography)) { $bibliography = [['type' => 'book', 'title' => '', 'author' => '', 'year' => '', 'publisher' => '', 'url' => '', 'notes' => '']]; }
@endphp

<div
    class="es-project-work es-page-enter"
    x-data="projectWorkspace({
        saveUrl: @js(route('student.projects.save', $project)),
        uploadUrl: @js(route('student.projects.upload', $project)),
        deleteFileUrl: @js(url('/student/projects/'.$project->id.'/files')),
        submitUrl: @js(route('student.projects.submit', $project)),
        csrf: @js(csrf_token()),
        canEdit: @json($canEdit),
        content: @js($submission->content ?? ''),
        sources: @js($sources),
        bibliography: @js($bibliography),
        files: @js($submission->files->map(fn ($f) => ['id' => $f->id, 'name' => $f->displayName(), 'url' => route('project-submission-files.show', [$project, $f])])->values()),
        allowsWrite: @json($project->allowsOnlineWrite()),
        allowsUpload: @json($project->allowsUpload()),
        requireSources: @json($project->require_sources),
        requireBibliography: @json($project->require_bibliography),
    })"
>
    <div class="es-container py-6 max-w-5xl">
        <a href="{{ route('student.projects.index') }}" class="es-link text-sm font-bold">← Mes projets</a>

        <header class="mt-4 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-es-muted">{{ $project->subject->name }}</p>
                    <h1 class="text-2xl md:text-3xl font-black text-es-ink">{{ $project->title }}</h1>
                    <p class="text-sm text-es-muted mt-1">{{ $project->typeIcon() }} {{ $project->typeLabel() }} · {{ $project->formatLabel() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold" :class="saveState === 'saved' ? 'text-emerald-600' : saveState === 'error' ? 'text-red-600' : 'text-es-muted'" x-text="saveLabel"></p>
                    @if ($project->due_at)
                        <p class="text-xs text-es-muted mt-1">Échéance {{ $project->due_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if ($submission->workflow_status === 'returned' && $submission->correction?->comment)
                <x-alert type="warning" class="mt-4" title="Renvoyé par le professeur">
                    {{ $submission->correction->comment }}
                </x-alert>
            @elseif ($submission->workflow_status === 'corrected' && $submission->correction?->score !== null)
                <x-alert type="success" class="mt-4" title="Projet corrigé">
                    Note : {{ number_format($submission->correction->score, 0) }}/100
                    @if ($submission->correction->comment)
                        — {{ $submission->correction->comment }}
                    @endif
                </x-alert>
            @elseif ($submission->workflow_status === 'submitted')
                <x-alert type="info" class="mt-4" title="Projet soumis">
                    En attente de correction par ton professeur.
                </x-alert>
            @endif
        </header>

        {{-- Onglets --}}
        <nav class="flex flex-wrap gap-2 mb-6 p-1 bg-stone-100 rounded-2xl w-fit" role="tablist">
            <button type="button" class="es-btn es-btn-sm" :class="tab === 'brief' ? 'es-btn-primary' : 'es-btn-secondary'" @click="tab = 'brief'">📋 Consignes</button>
            @if ($project->allowsOnlineWrite())
                <button type="button" class="es-btn es-btn-sm" :class="tab === 'work' ? 'es-btn-primary' : 'es-btn-secondary'" @click="tab = 'work'">✍️ Mon travail</button>
            @endif
            @if ($project->allowsUpload())
                <button type="button" class="es-btn es-btn-sm" :class="tab === 'files' ? 'es-btn-primary' : 'es-btn-secondary'" @click="tab = 'files'">📎 Fichiers</button>
            @endif
            @if ($project->require_sources)
                <button type="button" class="es-btn es-btn-sm" :class="tab === 'sources' ? 'es-btn-primary' : 'es-btn-secondary'" @click="tab = 'sources'">🔍 Sources</button>
            @endif
            @if ($project->require_bibliography)
                <button type="button" class="es-btn es-btn-sm" :class="tab === 'biblio' ? 'es-btn-primary' : 'es-btn-secondary'" @click="tab = 'biblio'">📚 Bibliographie</button>
            @endif
        </nav>

        {{-- Consignes --}}
        <section x-show="tab === 'brief'" x-cloak class="space-y-4">
            <div class="es-card p-5 md:p-6">
                <h2 class="font-extrabold text-lg mb-3">Consignes du professeur</h2>
                <div class="whitespace-pre-wrap text-es-ink leading-relaxed">{{ $project->instructions }}</div>
            </div>

            @if ($project->attachments->isNotEmpty())
                <div class="es-card p-5">
                    <h3 class="font-extrabold mb-3">Documents fournis</h3>
                    <ul class="space-y-2">
                        @foreach ($project->attachments as $attachment)
                            <li>
                                <a href="{{ route('project-media.show', [$project, $attachment]) }}" class="es-link font-bold" target="_blank">
                                    📎 {{ $attachment->displayName() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- Travail rédigé --}}
        @if ($project->allowsOnlineWrite())
            <section x-show="tab === 'work'" x-cloak>
                <div class="es-card p-5 md:p-6">
                    <h2 class="font-extrabold text-lg mb-3">Mon travail</h2>
                    <textarea
                        x-model="content"
                        @input.debounce.800ms="save()"
                        :disabled="!canEdit"
                        rows="18"
                        class="es-textarea w-full text-base leading-relaxed min-h-[420px]"
                        placeholder="Rédige ton compte rendu, ta recherche ou ton dossier ici…"
                    ></textarea>
                </div>
            </section>
        @endif

        {{-- Fichiers --}}
        @if ($project->allowsUpload())
            <section x-show="tab === 'files'" x-cloak>
                <div class="es-card p-5 md:p-6 space-y-4">
                    <h2 class="font-extrabold text-lg">Fichiers déposés</h2>
                    <template x-if="canEdit">
                        <div class="rounded-2xl border-2 border-dashed border-stone-200 p-6 text-center">
                            <input type="file" x-ref="fileInput" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx" @change="uploadFile($event)">
                            <p class="text-sm text-es-muted mb-3">PDF, Word ou PowerPoint (max 50 Mo)</p>
                            <button type="button" class="es-btn es-btn-secondary" @click="$refs.fileInput.click()">Téléverser un fichier</button>
                        </div>
                    </template>
                    <ul class="space-y-2" x-show="files.length">
                        <template x-for="file in files" :key="file.id">
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-4 py-3">
                                <a :href="file.url" class="es-link font-bold truncate" target="_blank" x-text="'📎 ' + file.name"></a>
                                <button type="button" x-show="canEdit" class="text-xs font-bold text-red-600" @click="removeFile(file.id)">Supprimer</button>
                            </li>
                        </template>
                    </ul>
                </div>
            </section>
        @endif

        {{-- Sources --}}
        @if ($project->require_sources)
            <section x-show="tab === 'sources'" x-cloak class="space-y-4">
                <div class="es-card p-5 md:p-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-extrabold text-lg">Sources documentaires</h2>
                            <p class="text-sm text-es-muted">Sites, livres, articles consultés pour ton projet.</p>
                        </div>
                        <button type="button" class="es-btn es-btn-secondary es-btn-sm" x-show="canEdit" @click="addSource()">+ Ajouter</button>
                    </div>
                    <template x-for="(source, index) in sources" :key="index">
                        <div class="rounded-2xl border border-stone-200 p-4 mb-3 space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="es-label text-xs">Type</label>
                                    <select x-model="source.type" @change="save()" :disabled="!canEdit" class="es-select text-sm">
                                        @foreach (config('project.source_types') as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="es-label text-xs">Titre *</label>
                                    <input type="text" x-model="source.title" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                                <div>
                                    <label class="es-label text-xs">Auteur</label>
                                    <input type="text" x-model="source.author" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                                <div>
                                    <label class="es-label text-xs">URL</label>
                                    <input type="url" x-model="source.url" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="es-label text-xs">Notes / extrait utile</label>
                                <textarea x-model="source.notes" @input.debounce.800ms="save()" :disabled="!canEdit" rows="2" class="es-textarea text-sm"></textarea>
                            </div>
                            <button type="button" x-show="canEdit && sources.length > 1" class="text-xs font-bold text-red-600" @click="sources.splice(index, 1); save()">Supprimer cette source</button>
                        </div>
                    </template>
                </div>
            </section>
        @endif

        {{-- Bibliographie --}}
        @if ($project->require_bibliography)
            <section x-show="tab === 'biblio'" x-cloak class="space-y-4">
                <div class="es-card p-5 md:p-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-extrabold text-lg">Bibliographie</h2>
                            <p class="text-sm text-es-muted">Références au format scolaire (auteur, titre, année…).</p>
                        </div>
                        <button type="button" class="es-btn es-btn-secondary es-btn-sm" x-show="canEdit" @click="addBiblio()">+ Ajouter</button>
                    </div>
                    <template x-for="(entry, index) in bibliography" :key="index">
                        <div class="rounded-2xl border border-stone-200 p-4 mb-3 space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="es-label text-xs">Type</label>
                                    <select x-model="entry.type" @change="save()" :disabled="!canEdit" class="es-select text-sm">
                                        @foreach (config('project.bibliography_types') as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="es-label text-xs">Titre *</label>
                                    <input type="text" x-model="entry.title" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                                <div>
                                    <label class="es-label text-xs">Auteur</label>
                                    <input type="text" x-model="entry.author" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                                <div>
                                    <label class="es-label text-xs">Année</label>
                                    <input type="text" x-model="entry.year" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="es-label text-xs">Éditeur / revue</label>
                                    <input type="text" x-model="entry.publisher" @input.debounce.800ms="save()" :disabled="!canEdit" class="es-input text-sm">
                                </div>
                            </div>
                            <button type="button" x-show="canEdit && bibliography.length > 1" class="text-xs font-bold text-red-600" @click="bibliography.splice(index, 1); save()">Supprimer</button>
                        </div>
                    </template>
                </div>
            </section>
        @endif

        @if ($canEdit)
            <footer class="mt-8 flex flex-wrap items-center justify-between gap-4 sticky bottom-4 es-card p-4 shadow-lg">
                <p class="text-sm text-es-muted">Pense à compléter ton travail, tes sources et ta bibliographie.</p>
                <button type="button" class="es-btn es-btn-primary" @click="submitProject()" :disabled="submitting">
                    <span x-show="!submitting">Soumettre le projet</span>
                    <span x-show="submitting">Envoi…</span>
                </button>
            </footer>
        @endif
    </div>
</div>

@push('scripts')
    @vite('resources/js/project-workspace.js')
@endpush
@endsection
