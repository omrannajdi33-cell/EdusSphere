@php
    $workspaceData = $canvasAnswer?->content['workspace'] ?? [];
    $passage = $page->passageText();
    $audio = $page->audioMediaFile;
    $rtl = $page->isRtl();
    $textHidden = (bool) ($workspaceData['text_hidden'] ?? false);
    $recordingStudent = $student ?? auth()->user()?->student;
    $recordingUrl = null;

    if (! empty($workspaceData['recording_path']) && $recordingStudent) {
        $recordingUrl = route('activities.recording.show', [$activity, $recordingStudent], absolute: false).'?path='.urlencode($workspaceData['recording_path']);
    } elseif (! empty($workspaceData['recording_url']) && ! ($correctionMode ?? false)) {
        $recordingUrl = $workspaceData['recording_url'];
    }
@endphp

<div class="es-subject-workspace" data-workspace-root>
    @if ($page->isReading())
        <div class="es-reading-panel" data-reading-panel data-text-hidden="{{ $textHidden ? '1' : '0' }}">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                @if ($audio)
                    <audio controls class="es-reading-audio max-w-full h-9" src="{{ route('activity-media.show', [$activity, $audio], false) }}" preload="metadata"></audio>
                @endif
                <button type="button" class="es-btn es-btn-secondary es-btn-sm workspace-toggle-text" data-label-show="📖 Afficher le texte" data-label-hide="🙈 Masquer le texte">
                    {{ $textHidden ? '📖 Afficher le texte' : '🙈 Masquer le texte' }}
                </button>
            </div>

            <div class="es-reading-passage rounded-2xl border border-stone-200 bg-stone-50 p-4 md:p-5 {{ $textHidden ? 'hidden' : '' }}" data-reading-text @if ($rtl) dir="rtl" @endif>
                <p class="text-base md:text-lg leading-relaxed whitespace-pre-wrap font-medium text-es-ink {{ $page->isRecitation() ? 'text-right' : '' }}">{{ $passage }}</p>
            </div>

            @unless ($page->isRecitation())
                <div class="mt-4">
                    <label class="es-label text-sm">Mes notes / réponses</label>
                    <textarea
                        class="player-workspace-notes es-textarea w-full min-h-[120px] text-sm"
                        placeholder="Écris tes réponses à la compréhension…"
                        @if ($readOnly && ! $correctionMode) readonly @endif
                    >{{ $workspaceData['notes'] ?? '' }}</textarea>
                </div>
            @endunless
        </div>

        @if ($page->recordsVoice())
            @include('student.activities.partials.oral-recording-panel', [
                'activity' => $activity,
                'page' => $page,
                'workspaceData' => $workspaceData,
                'recordingUrl' => $recordingUrl,
                'readOnly' => $readOnly,
                'correctionMode' => $correctionMode,
                'recitationMode' => $page->isRecitation(),
            ])
        @endif
    @endif

    @if ($page->isOral())
        @include('student.activities.partials.oral-recording-panel', [
            'activity' => $activity,
            'page' => $page,
            'workspaceData' => $workspaceData,
            'recordingUrl' => $recordingUrl,
            'readOnly' => $readOnly,
            'correctionMode' => $correctionMode,
            'recitationMode' => false,
        ])
    @endif

    @if ($page->isRichDocument())
        @php $richMode = $workspaceData['rich_mode'] ?? 'text'; @endphp
        <div class="es-rich-panel" data-rich-panel data-rich-mode="{{ $richMode }}">
            @unless ($readOnly && ! $correctionMode)
                <div class="flex gap-1 mb-3 p-1 bg-stone-100 rounded-xl w-fit">
                    <button type="button" class="rich-tab es-btn es-btn-sm {{ $richMode === 'text' ? 'es-btn-primary' : 'es-btn-secondary' }}" data-mode="text">📄 Texte</button>
                    <button type="button" class="rich-tab es-btn es-btn-sm {{ $richMode === 'draw' ? 'es-btn-primary' : 'es-btn-secondary' }}" data-mode="draw">✏️ Dessin</button>
                </div>
                <div class="flex flex-wrap gap-1 mb-2 rich-toolbar {{ $richMode === 'draw' ? 'hidden' : '' }}" data-rich-toolbar>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm rich-cmd" data-cmd="bold"><b>G</b></button>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm rich-cmd" data-cmd="italic"><i>I</i></button>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm rich-cmd" data-cmd="insertUnorderedList">• Liste</button>
                </div>
            @endunless

            <div class="player-rich-editor es-rich-editor rounded-2xl border border-stone-200 min-h-[280px] p-4 text-base leading-relaxed {{ $richMode === 'draw' ? 'hidden' : '' }}"
                contenteditable="{{ ($readOnly && ! $correctionMode) ? 'false' : 'true' }}"
                data-rich-editor>{!! $workspaceData['rich_html'] ?? '' !!}</div>

            <div class="player-workspace-draw {{ $richMode === 'text' ? 'hidden' : '' }}" data-rich-draw-wrap>
                {{-- le canvas standard est rendu par player-shell autour --}}
            </div>
        </div>
    @endif
</div>
