@php
    $workspaceData = $canvasAnswer?->content['workspace'] ?? [];
    $passage = $page->passageText();
    $audio = $page->audioMediaFile;
    $rtl = $page->isRtl();
    $textHidden = (bool) ($workspaceData['text_hidden'] ?? false);
    $recordingUrl = $workspaceData['recording_url'] ?? (
        ! empty($workspaceData['recording_path'])
            ? route('student.activities.recording', $activity, absolute: false).'?path='.urlencode($workspaceData['recording_path'])
            : null
    );
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
                <p class="text-base md:text-lg leading-relaxed whitespace-pre-wrap font-medium text-es-ink">{{ $passage }}</p>
            </div>

            <div class="mt-4">
                <label class="es-label text-sm">Mes notes / réponses</label>
                <textarea
                    class="player-workspace-notes es-textarea w-full min-h-[120px] text-sm"
                    placeholder="Écris tes réponses à la compréhension…"
                    @if ($readOnly && ! $correctionMode) readonly @endif
                >{{ $workspaceData['notes'] ?? '' }}</textarea>
            </div>
        </div>
    @endif

    @if ($page->isOral())
        <div class="es-oral-panel space-y-4" data-oral-panel data-recording-path="{{ $workspaceData['recording_path'] ?? '' }}" data-recording-kind="{{ $workspaceData['recording_kind'] ?? 'audio' }}">
            <p class="text-sm text-es-muted">Enregistre ta réponse orale (audio ou courte vidéo).</p>

            @unless ($readOnly && ! $correctionMode)
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm oral-record-audio">🎤 Audio</button>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm oral-record-video">📹 Vidéo</button>
                    <button type="button" class="es-btn es-btn-secondary es-btn-sm oral-stop hidden">⏹ Arrêter</button>
                </div>
            @endunless

            <p class="text-xs font-semibold text-es-muted oral-status" aria-live="polite"></p>

            <div class="oral-preview rounded-2xl border border-stone-200 bg-stone-50 p-3 min-h-[80px] {{ empty($recordingUrl) ? 'hidden' : '' }}" data-oral-preview>
                @if ($recordingUrl)
                    @if (($workspaceData['recording_kind'] ?? 'audio') === 'video')
                        <video controls class="w-full max-h-64 rounded-xl" src="{{ $recordingUrl }}"></video>
                    @else
                        <audio controls class="w-full" src="{{ $recordingUrl }}"></audio>
                    @endif
                @endif
            </div>

            <video class="oral-live-video hidden w-full max-h-48 rounded-xl bg-black" autoplay muted playsinline></video>
        </div>
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
