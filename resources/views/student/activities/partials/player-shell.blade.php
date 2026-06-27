@php
    $previewMode = $previewMode ?? false;
    $correctionMode = $correctionMode ?? false;
    $student = $student ?? null;
    $correction = $correction ?? null;
    $pages = $activity->pages;
    $totalPages = max(1, $pages->count());
    $startPage = min($progression?->last_page ?? 1, $totalPages);
    $savedAnswers = $answers ?? collect();
    $isSubmitted = $progression && $progression->workflow_status === 'submitted';
    $isReturned = $progression && $progression->workflow_status === 'returned';
    $isCorrected = $progression && $progression->workflow_status === 'corrected';
    $isLocked = $progression && in_array($progression->workflow_status, ['submitted', 'corrected'], true);
    $readOnly = $previewMode || $isLocked || $correctionMode;
    $saveUrlOverride = $saveUrl ?? null;
    $submitUrlOverride = $submitUrl ?? null;
    $canSubmit = ! $previewMode && ! $correctionMode && (! $isLocked || $isReturned);
    $focusMode = $focusMode ?? false;
    $examMode = $examMode ?? false;
    $lessonAnnotations = $lessonAnnotations ?? collect();
    $linkedLesson = $activity->relationLoaded('lesson') ? $activity->lesson : null;
@endphp

<div
    id="activity-player"
    class="overflow-hidden {{ ($focusMode ?? false) ? 'es-focus-player' : 'es-card' }}"
    data-activity-id="{{ $activity->id }}"
    data-csrf-token="{{ csrf_token() }}"
    data-save-url="{{ $saveUrlOverride ?? (($previewMode || $correctionMode || $isLocked) ? '' : route('student.activities.save', $activity, false)) }}"
    data-submit-url="{{ $submitUrlOverride ?? ($canSubmit ? route('student.activities.submit', $activity, false) : '') }}"
    data-correction-url="{{ $correctionMode && $student ? route('admin.activities.corrections.save', [$activity, $student], false) : '' }}"
    data-preview="{{ $previewMode ? '1' : '0' }}"
    data-correction="{{ $correctionMode ? '1' : '0' }}"
    data-readonly="{{ $readOnly && ! $correctionMode ? '1' : '0' }}"
    data-returned="{{ $isReturned ? '1' : '0' }}"
    data-recording-url="{{ $recordingUrlOverride ?? (auth()->check() && !($previewMode ?? false) ? route('student.activities.recording.upload', $activity, false) : '') }}"
    data-initial-page="{{ $startPage }}"
    data-total-pages="{{ $totalPages }}"
    data-home-url="{{ route('student.dashboard', absolute: false) }}"
    role="application"
    aria-label="Activité : {{ $activity->title }}"
>
    @if ($focusMode ?? false)
        <div class="es-focus-player-status px-4 py-3 flex flex-wrap items-center justify-between gap-3 border-b border-stone-200/80">
            <span id="player-save-status" class="text-sm font-semibold text-es-muted" aria-live="polite"></span>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" id="player-save-retry" class="hidden text-sm font-bold text-es-primary underline">Réessayer</button>
                <span id="player-page-indicator" class="text-sm font-bold text-es-primary tabular-nums" aria-live="polite">
                    Page {{ $startPage }} / {{ $totalPages }}
                </span>
            </div>
        </div>
    @else
    <div class="border-b border-stone-200 px-4 py-3 flex flex-wrap items-center justify-between gap-3 bg-stone-50">
        <div>
            <p class="text-sm font-bold text-es-muted">{{ $activity->subject->name }}</p>
            <h2 class="text-lg font-extrabold text-es-ink">{{ $activity->title }}</h2>
            @if ($correctionMode && $student)
                <p class="text-sm font-semibold text-red-600 mt-1">Mode correction — {{ $student->full_name }}</p>
            @elseif ($isReturned)
                <p class="text-sm font-semibold text-amber-600 mt-1">Renvoyée par le prof — tu peux modifier et resoumettre</p>
                @if ($correction?->comment)
                    <p class="text-sm text-es-muted mt-1">{{ $correction->comment }}</p>
                @endif
            @elseif ($isSubmitted)
                <p class="text-sm font-semibold text-amber-600 mt-1">Activité soumise — en attente de correction</p>
            @elseif ($isCorrected)
                <p class="text-sm font-semibold text-emerald-600 mt-1">Activité corrigée ✓</p>
                @if ($correction?->score !== null)
                    <p class="text-sm font-bold text-es-primary mt-1">Ta note : {{ number_format($correction->score, 0) }}/100</p>
                @endif
                @if ($correction?->comment)
                    <p class="text-sm text-es-muted mt-1">{{ $correction->comment }}</p>
                @endif
            @endif
        </div>
        <div class="flex items-center gap-3 flex-wrap justify-end">
            @if ($linkedLesson && $linkedLesson->mediaFiles->isNotEmpty() && ! $previewMode && ! $correctionMode)
                <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-lesson-open>📚 Voir la leçon</button>
            @endif
            <span id="player-save-status" class="text-sm font-semibold text-es-muted" aria-live="polite"></span>
            <button type="button" id="player-save-retry" class="hidden text-sm font-bold text-es-primary underline">Réessayer</button>
            <span id="player-page-indicator" class="text-sm font-bold text-es-primary" aria-live="polite">
                Page {{ $startPage }} / {{ $totalPages }}
            </span>
        </div>
    </div>
    @endif

    @if ($pages->isEmpty())
        <div class="p-8 es-empty">
            <p class="font-extrabold">Cette activité n'a pas encore d'étapes.</p>
        </div>
    @else
        <div id="player-toolbar" class="border-b border-stone-200 px-4 py-3 flex flex-wrap gap-2 {{ $correctionMode ? '' : 'hidden' }}" role="toolbar" aria-label="Outils">
            <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="pen" aria-pressed="true">
                {{ $correctionMode ? '🖊 Encre rouge' : '✏️ Dessiner' }}
            </button>
            @unless ($correctionMode)
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="highlight" aria-pressed="false">🖍 Surligner</button>
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="erase" aria-pressed="false">🧽 Effacer</button>
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="text" aria-pressed="false">📝 Écrire</button>
                <button type="button" id="player-clear-canvas" class="es-btn es-btn-secondary es-btn-sm">Effacer tout</button>
            @endunless
        </div>

        <div class="relative">
            @foreach ($pages as $page)
                @php
                    $pageAnswers = $savedAnswers->get($page->id, collect());
                    $canvasAnswer = $pageAnswers->first(fn ($a) => $examMode ? $a->exam_question_id === null : $a->question_id === null);
                    $canvasData = $canvasAnswer?->content['canvas'] ?? null;
                    $teacherStrokes = $canvasAnswer?->content['teacher_strokes'] ?? null;
                    $questionValues = $pageAnswers
                        ->filter(fn ($a) => $examMode ? $a->exam_question_id !== null : $a->question_id !== null)
                        ->mapWithKeys(fn ($a) => [
                            ($examMode ? $a->exam_question_id : $a->question_id) => $a->content['value'] ?? null,
                        ]);
                    $workspaceData = $canvasAnswer?->content['workspace'] ?? null;
                    $showCanvas = ($page->needsCanvas() && ! $page->isOral() && ! $page->isReading()) || ($correctionMode && $page->needsCanvas());
                    $pageMeta = config('activity.page_types.'.$page->type, []);
                    $scrollHeight = (int) ($page->content['scroll_height'] ?? 3200);
                @endphp
                <section
                    class="player-page {{ ($focusMode ?? false) ? 'es-focus-player-page' : '' }} p-4 md:p-6 space-y-5 {{ $page->page_order !== $startPage ? 'hidden' : '' }}"
                    data-page
                    data-page-id="{{ $page->id }}"
                    data-page-order="{{ $page->page_order }}"
                    data-page-type="{{ $page->type }}"
                    data-scroll-height="{{ $scrollHeight }}"
                    data-needs-canvas="{{ $showCanvas ? '1' : '0' }}"
                    aria-labelledby="page-title-{{ $page->id }}"
                    @if ($page->page_order !== $startPage) hidden @endif
                >
                    <header class="{{ ($focusMode ?? false) ? 'es-focus-page-header' : '' }}">
                        <span class="text-xs font-bold uppercase tracking-wide text-es-muted">{{ is_array($pageMeta) ? ($pageMeta['label'] ?? '') : '' }}</span>
                        <h3 id="page-title-{{ $page->id }}" class="text-lg md:text-xl font-extrabold text-es-ink mt-1.5">{{ $page->title }}</h3>
                        @if ($page->content['body'] ?? null)
                            <p class="mt-3 text-base text-es-muted leading-relaxed whitespace-pre-wrap">{{ $page->content['body'] }}</p>
                        @endif
                    </header>

                    @if ($page->isSubjectWorkspace())
                        @include('student.activities.partials.workspace-page', [
                            'activity' => $activity,
                            'page' => $page,
                            'canvasAnswer' => $canvasAnswer,
                            'readOnly' => $readOnly,
                            'correctionMode' => $correctionMode,
                            'student' => $student ?? auth()->user()?->student,
                        ])
                    @endif

                    @if ($showCanvas)
                        @if ($page->isMathScroll())
                        <div class="es-math-scroll-wrap overflow-y-auto rounded-2xl border border-stone-200 bg-white player-workspace" style="max-height: min(70vh, 640px);">
                            <div class="relative" style="min-height: {{ $scrollHeight }}px">
                        @else
                        <div class="player-workspace relative rounded-2xl border border-stone-200 bg-white min-h-[420px] overflow-hidden">
                        @endif
                            @if ($page->isPdfWorksheet() && $page->mediaFile)
                                <iframe
                                    src="{{ route('activity-media.show', [$activity, $page->mediaFile]) }}#toolbar=0"
                                    class="absolute inset-0 w-full h-full pointer-events-none border-0"
                                    title="Document PDF"
                                ></iframe>
                            @endif
                            <canvas
                                class="player-canvas-student absolute inset-0 w-full h-full touch-none z-20"
                                aria-label="Travail de l'élève"
                                @if ($canvasData && ! $correctionMode) data-initial="{{ json_encode($canvasData['strokes'] ?? []) }}" @endif
                                @if ($canvasData && $correctionMode) data-initial="{{ json_encode($canvasData['strokes'] ?? []) }}" data-readonly="1" @endif
                            ></canvas>
                            <canvas
                                class="player-canvas-teacher absolute inset-0 w-full h-full touch-none z-30 {{ $correctionMode ? '' : 'hidden pointer-events-none' }}"
                                aria-label="Correction professeur"
                                @if ($teacherStrokes) data-initial="{{ json_encode($teacherStrokes) }}" @endif
                            ></canvas>
                            <textarea
                                class="player-notes absolute inset-0 w-full h-full min-h-[420px] resize-none rounded-2xl p-4 bg-transparent z-10 hidden"
                                placeholder="Écris ici…"
                                aria-label="Zone d'écriture"
                                @if ($readOnly && ! $correctionMode) readonly @endif
                            >{{ $canvasData['notes'] ?? '' }}</textarea>
                        @if ($page->isMathScroll())
                            </div>
                        @endif
                        </div>
                    @endif

                    @foreach ($page->questions as $question)
                        @php
                            $studentValue = $questionValues->get($question->id);
                            $review = ($correctionMode || $isCorrected)
                                ? \App\Support\QuestionGrader::evaluate($question, $studentValue)
                                : null;
                        @endphp
                        <x-activity-question
                            :question="$question"
                            :value="$studentValue"
                            :readonly="$readOnly"
                            :review="$review"
                        />
                    @endforeach
                </section>
            @endforeach
        </div>

        <footer class="border-t border-stone-200 px-4 py-4 flex flex-wrap items-center justify-between gap-3 {{ ($focusMode ?? false) ? 'es-focus-player-footer bg-white' : 'bg-stone-50' }}">
            @if ($correctionMode)
                <a href="{{ route('admin.activities.submissions', $activity) }}" class="es-link text-sm font-bold">← Copies</a>
            @elseif ($previewMode)
                <a href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 2]) }}" class="es-link text-sm font-bold">← Retour</a>
            @elseif (! ($focusMode ?? false))
                <a href="{{ route('student.activities.index') }}" class="es-link text-sm font-bold">← Retour</a>
            @else
                <span class="text-sm font-bold text-es-muted">Mode verrouillé</span>
            @endif
            <div class="flex gap-2">
                <button type="button" id="player-prev" class="es-btn es-btn-secondary" disabled>Précédent</button>
                <button type="button" id="player-next" class="es-btn es-btn-primary">Suivant</button>
                @if ($canSubmit)
                    <button type="button" id="player-submit" class="es-btn es-btn-primary hidden">{{ ($examMode ?? false) ? 'Soumettre l\'examen' : 'Soumettre l\'activité' }}</button>
                @endif
            </div>
        </footer>
    @endif
</div>

@if ($linkedLesson && $linkedLesson->mediaFiles->isNotEmpty())
    <div class="hidden fixed inset-0 z-[60] bg-es-ink/60 p-4 md:p-8" data-lesson-panel role="dialog" aria-modal="true" aria-label="Leçon associée">
        <div class="es-card max-w-4xl mx-auto max-h-full overflow-y-auto p-4 md:p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <p class="text-xs font-bold uppercase text-es-muted">Leçon de référence</p>
                    <h2 class="text-xl font-extrabold">{{ $linkedLesson->title }}</h2>
                </div>
                <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-lesson-close>Fermer</button>
            </div>
            @foreach ($linkedLesson->mediaFiles as $media)
                @php
                    $ann = $lessonAnnotations->get($media->id);
                    $annPages = $ann?->content['pages'] ?? [];
                @endphp
                <div class="mb-6">
                    <p class="text-sm font-bold text-es-muted mb-2">{{ $media->displayName() }}</p>
                    <x-document-viewer
                        :file-url="route('lesson-media.show', [$linkedLesson, $media])"
                        :doc-kind="$media->source_kind ?? 'pdf'"
                        :save-url="route('student.lessons.annotations.save', $linkedLesson)"
                        :read-only="$readOnly"
                        :initial-annotations="$annPages"
                        :media-file-id="$media->id"
                    />
                </div>
            @endforeach
        </div>
    </div>
    <script>
    document.querySelector('[data-lesson-open]')?.addEventListener('click', () => {
        document.querySelector('[data-lesson-panel]')?.classList.remove('hidden');
        if (window.initDocumentViewers) window.initDocumentViewers(document.querySelector('[data-lesson-panel]'));
    });
    document.querySelector('[data-lesson-close]')?.addEventListener('click', () => {
        document.querySelector('[data-lesson-panel]')?.classList.add('hidden');
    });
    </script>
@endif

@unless ($focusMode ?? false)
@push('scripts')
    @vite('resources/js/activity-player.js')
@endpush
@endunless
