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
    $playerRootClass = match (true) {
        ($focusMode ?? false) => 'ap-player flex-1 flex flex-col min-h-0 bg-white text-es-ink',
        $correctionMode => 'ap-correction-player flex flex-col flex-1 min-h-0 bg-white text-es-ink rounded-2xl border border-stone-200 shadow-sm overflow-hidden',
        default => 'es-card overflow-hidden',
    };
@endphp

<div
    id="activity-player"
    class="{{ $playerRootClass }}"
    data-activity-id="{{ $activity->id }}"
    data-csrf-token="{{ csrf_token() }}"
    data-save-url="{{ $saveUrlOverride ?? (($previewMode || $correctionMode || $isLocked) ? '' : route('student.activities.save', $activity, false)) }}"
    data-submit-url="{{ $submitUrlOverride ?? ($canSubmit ? route('student.activities.submit', $activity, false) : '') }}"
    data-correction-url="{{ $correctionMode && $student ? route('admin.activities.corrections.save', [$activity, $student], false) : '' }}"
    data-preview="{{ $previewMode ? '1' : '0' }}"
    data-correction="{{ $correctionMode ? '1' : '0' }}"
    data-readonly="{{ $readOnly && ! $correctionMode ? '1' : '0' }}"
    data-returned="{{ $isReturned ? '1' : '0' }}"
    data-recording-url="{{ $recordingUrlOverride ?? (auth()->check() && !($previewMode ?? false) ? route('student.activities.recording.upload', $activity) : '') }}"
    @if ($activity->requiresResultPhoto())
    data-require-result-photo="1"
    data-result-photo-upload-url="{{ auth()->check() && !($previewMode ?? false) && !($correctionMode ?? false) && !($readOnly && !($isReturned ?? false)) ? route('student.activities.result-photo.upload', $activity) : '' }}"
    data-result-photo-delete-url="{{ auth()->check() && !($previewMode ?? false) && !($correctionMode ?? false) && !($readOnly && !($isReturned ?? false)) ? route('student.activities.result-photo.delete', $activity) : '' }}"
    data-result-photo-show-url="{{ ($student ?? auth()->user()?->student) ? route('activities.result-photo.show', [$activity, $student ?? auth()->user()->student]) : '' }}"
    data-result-photo-count="{{ count($progression?->resultPhotoPaths() ?? []) }}"
    @endif
    data-initial-page="{{ $startPage }}"
    data-total-pages="{{ $totalPages }}"
    data-home-url="{{ route('student.dashboard', absolute: false) }}"
    role="application"
    aria-label="Activité : {{ $activity->title }}"
>
    <header class="ap-player-header shrink-0 border-b border-stone-200/90 bg-stone-50/60 px-4 py-2.5 md:px-5 flex items-center justify-between gap-3">
        @if ($correctionMode)
            <p class="text-xs font-bold uppercase tracking-wider text-es-muted">Copie de l'élève</p>
            <span id="player-page-indicator" class="inline-flex items-center rounded-lg bg-white border border-stone-200 px-3 py-1 text-sm font-bold text-es-primary tabular-nums" aria-live="polite">
                Page {{ $startPage }} / {{ $totalPages }}
            </span>
        @elseif ($focusMode ?? false)
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-es-muted">{{ $activity->subject->name }}</p>
                <h2 class="text-base md:text-lg font-black text-es-ink truncate">{{ $activity->title }}</h2>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span id="player-save-status" class="text-sm font-semibold text-es-muted" aria-live="polite"></span>
                <button type="button" id="player-save-retry" class="hidden text-sm font-bold text-es-primary underline">Réessayer</button>
                <span id="player-page-indicator" class="ap-page-badge tabular-nums" aria-live="polite">
                    {{ $startPage }} / {{ $totalPages }}
                </span>
            </div>
        @else
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-es-muted">{{ $activity->subject->name }}</p>
                <h2 class="text-lg font-extrabold text-es-ink">{{ $activity->title }}</h2>
                @if ($isReturned)
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
                <span id="player-page-indicator" class="text-sm font-bold text-es-primary tabular-nums" aria-live="polite">
                    Page {{ $startPage }} / {{ $totalPages }}
                </span>
            </div>
        @endif
    </header>

    @if ($pages->isEmpty())
        <div class="flex-1 p-8 es-empty">
            <p class="font-extrabold">Cette activité n'a pas encore d'étapes.</p>
        </div>
    @else
        <div id="player-toolbar" class="shrink-0 border-b border-stone-200 px-4 py-2.5 flex flex-wrap gap-2 bg-amber-50/80 {{ $correctionMode ? '' : 'hidden' }}" role="toolbar" aria-label="Outils">
            @if ($correctionMode)
                <span class="text-xs font-bold text-amber-900 self-center mr-1">Encre rouge :</span>
            @endif
            <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="pen" aria-pressed="true">
                {{ $correctionMode ? '🖊 Encre rouge' : '✏️ Dessiner' }}
            </button>
            @unless ($correctionMode)
                <button type="button" class="player-tool player-tool-pan es-btn es-btn-secondary es-btn-sm hidden" data-tool="pan" aria-pressed="false">✋ Déplacer</button>
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="highlight" aria-pressed="false">🖍 Surligner</button>
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="erase" aria-pressed="false">🧽 Effacer</button>
                <button type="button" class="player-tool es-btn es-btn-secondary es-btn-sm" data-tool="text" aria-pressed="false">📝 Écrire</button>
                <button type="button" id="player-clear-canvas" class="es-btn es-btn-secondary es-btn-sm">Effacer tout</button>
            @endunless
        </div>

        <div class="ap-player-body flex-1 min-h-0 {{ $correctionMode ? 'flex flex-col' : 'relative' }}">
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
                    $showCanvas = ($page->needsCanvas() && ! $page->isOral() && ! $page->isReading()) || ($correctionMode && $page->needsCanvas());
                    $pageMeta = config('activity.page_types.'.$page->type, []);
                    $scrollHeight = (int) ($page->content['scroll_height'] ?? 3200);
                    $hasQuestions = $page->questions->isNotEmpty();
                    $hasBody = ! empty($page->content['body']);
                    $isFullscreenSheet = $page->isFullscreenSheet();
                    $hasSourcePane = $page->isSubjectWorkspace() || $showCanvas || $hasBody;
                    $useSplit = $hasQuestions && $hasSourcePane && ! $isFullscreenSheet;
                @endphp
                <section
                    class="player-page {{ $correctionMode ? 'ap-correction-page flex flex-col flex-1 min-h-0' : 'ap-player-page absolute inset-0 flex flex-col min-h-0' }} {{ $isFullscreenSheet ? 'ap-page-sheet-mode' : '' }} {{ $page->page_order !== $startPage ? 'hidden' : '' }}"
                    data-page
                    data-page-id="{{ $page->id }}"
                    data-page-order="{{ $page->page_order }}"
                    data-page-type="{{ $page->type }}"
                    data-scroll-height="{{ $scrollHeight }}"
                    data-needs-canvas="{{ $showCanvas ? '1' : '0' }}"
                    data-fullscreen-sheet="{{ $isFullscreenSheet ? '1' : '0' }}"
                    data-split-layout="{{ $useSplit ? '1' : '0' }}"
                    aria-labelledby="page-title-{{ $page->id }}"
                    @if ($page->page_order !== $startPage) hidden @endif
                >
                    @if ($isFullscreenSheet)
                        <div class="shrink-0 px-4 md:px-6 py-2.5 border-b border-stone-200/80 bg-stone-50/90 flex flex-wrap items-center justify-between gap-2 ap-sheet-chrome">
                            <div class="min-w-0">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-es-muted">{{ is_array($pageMeta) ? ($pageMeta['label'] ?? '') : '' }}</span>
                                <h3 id="page-title-{{ $page->id }}" class="text-sm md:text-base font-extrabold text-es-ink truncate">{{ $page->title }}</h3>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                @if ($hasBody || $page->title)
                                    <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-page-brief-open="{{ $page->id }}">📋 Consignes</button>
                                @endif
                                @if ($hasQuestions)
                                    <button type="button" class="es-btn es-btn-primary es-btn-sm" data-page-questions-open="{{ $page->id }}">❓ Questions ({{ $page->questions->count() }})</button>
                                @endif
                            </div>
                        </div>

                        @if ($showCanvas)
                            @include('student.activities.partials.player-canvas', [
                                'page' => $page,
                                'activity' => $activity,
                                'canvasData' => $canvasData,
                                'teacherStrokes' => $teacherStrokes,
                                'readOnly' => $readOnly,
                                'correctionMode' => $correctionMode,
                                'scrollHeight' => $scrollHeight,
                                'fullscreen' => true,
                            ])
                        @endif

                        @if ($hasBody || $page->title)
                            <div class="hidden fixed inset-0 z-[70] bg-es-ink/50 p-4 md:p-8 flex items-start justify-center overflow-y-auto" data-page-brief="{{ $page->id }}" role="dialog" aria-modal="true" aria-labelledby="brief-title-{{ $page->id }}">
                                <div class="es-card w-full max-w-lg p-5 md:p-6 my-auto shadow-xl">
                                    <div class="flex items-start justify-between gap-4 mb-4">
                                        <div>
                                            <p class="text-xs font-bold uppercase text-es-muted">Consignes</p>
                                            <h4 id="brief-title-{{ $page->id }}" class="text-xl font-black text-es-ink mt-1">{{ $page->title }}</h4>
                                        </div>
                                        <button type="button" class="es-btn es-btn-secondary es-btn-sm shrink-0" data-page-brief-close>Fermer</button>
                                    </div>
                                    @if ($hasBody)
                                        <p class="text-base text-es-ink leading-relaxed whitespace-pre-wrap">{{ $page->content['body'] }}</p>
                                    @else
                                        <p class="text-sm text-es-muted">Lis le document et complète les exercices directement sur la feuille.</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($hasQuestions)
                            <div class="hidden fixed inset-0 z-[70] bg-es-ink/50 p-4 md:p-8 flex items-start justify-center overflow-y-auto" data-page-questions-panel="{{ $page->id }}" role="dialog" aria-modal="true" aria-label="Questions">
                                <div class="es-card w-full max-w-2xl p-5 md:p-6 my-auto max-h-[min(90vh,720px)] overflow-y-auto shadow-xl">
                                    <div class="flex items-start justify-between gap-4 mb-4 sticky top-0 bg-white pb-3 border-b border-stone-100">
                                        <h4 class="text-xl font-black text-es-ink">Questions</h4>
                                        <button type="button" class="es-btn es-btn-secondary es-btn-sm shrink-0" data-page-questions-close>Fermer</button>
                                    </div>
                                    <div class="space-y-4">
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
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                    <div class="shrink-0 px-4 md:px-6 py-3 border-b border-stone-100 ap-page-titlebar">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-es-muted">{{ is_array($pageMeta) ? ($pageMeta['label'] ?? '') : '' }}</span>
                        <h3 id="page-title-{{ $page->id }}" class="text-lg font-extrabold text-es-ink mt-0.5">{{ $page->title }}</h3>
                        @if ($hasBody && ! $useSplit)
                            <p class="mt-2 text-sm text-es-muted leading-relaxed whitespace-pre-wrap">{{ $page->content['body'] }}</p>
                        @endif
                    </div>

                    @if ($useSplit)
                        <div class="ap-page-split flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-2">
                            <div class="ap-page-pane ap-page-source overflow-y-auto px-4 md:px-6 py-4 md:py-5 border-b lg:border-b-0 lg:border-r border-stone-200/80 bg-stone-50/40">
                                @if ($hasBody)
                                    <div class="ap-pane-label">Consignes</div>
                                    <p class="text-sm md:text-base text-es-ink leading-relaxed whitespace-pre-wrap mb-4">{{ $page->content['body'] }}</p>
                                @endif

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
                                    @include('student.activities.partials.player-canvas', [
                                        'page' => $page,
                                        'activity' => $activity,
                                        'canvasData' => $canvasData,
                                        'teacherStrokes' => $teacherStrokes,
                                        'readOnly' => $readOnly,
                                        'correctionMode' => $correctionMode,
                                        'scrollHeight' => $scrollHeight,
                                        'splitMode' => true,
                                    ])
                                @endif
                            </div>

                            <div class="ap-page-pane ap-page-questions overflow-y-auto px-4 md:px-6 py-4 md:py-5 space-y-4 bg-white">
                                <div class="ap-pane-label">Questions</div>
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
                            </div>
                        </div>
                    @else
                        <div class="ap-page-single flex-1 min-h-0 overflow-y-auto px-4 md:px-6 py-4 md:py-5 space-y-5">
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
                                @include('student.activities.partials.player-canvas', [
                                    'page' => $page,
                                    'activity' => $activity,
                                    'canvasData' => $canvasData,
                                    'teacherStrokes' => $teacherStrokes,
                                    'readOnly' => $readOnly,
                                    'correctionMode' => $correctionMode,
                                    'scrollHeight' => $scrollHeight,
                                    'splitMode' => false,
                                ])
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
                        </div>
                    @endif
                    @endif
                </section>
            @endforeach
        </div>

        @if ($activity->requiresResultPhoto() && ! ($correctionMode ?? false))
            <div class="shrink-0 border-t border-sky-100 px-4 md:px-5 py-3 bg-sky-50/40 hidden" data-result-photo-wrapper>
                @include('student.activities.partials.result-photo-panel', [
                    'activity' => $activity,
                    'progression' => $progression,
                    'student' => $student ?? auth()->user()?->student,
                    'readOnly' => $readOnly,
                ])
            </div>
        @endif

        <footer class="ap-player-footer shrink-0 border-t border-stone-200 px-4 md:px-5 py-2.5 flex flex-wrap items-center gap-2 bg-stone-50/60 {{ $correctionMode ? 'justify-end' : 'justify-between' }}">
            @unless ($correctionMode)
                @if ($previewMode)
                    <a href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 2]) }}" class="es-link text-sm font-bold">← Retour</a>
                @elseif (! ($focusMode ?? false))
                    <a href="{{ route('student.activities.index') }}" class="es-link text-sm font-bold">← Retour</a>
                @else
                    <span class="text-xs font-semibold text-es-muted uppercase tracking-wide">Mode verrouillé</span>
                @endif
            @endunless
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
