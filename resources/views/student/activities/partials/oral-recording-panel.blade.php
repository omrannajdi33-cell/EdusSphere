@props([
    'activity',
    'page',
    'workspaceData' => [],
    'recordingUrl' => null,
    'readOnly' => false,
    'correctionMode' => false,
    'recitationMode' => false,
])

<div
    class="es-oral-panel space-y-4 {{ $recitationMode ? 'es-recitation-voice mt-5 pt-5 border-t border-stone-200' : '' }}"
    data-oral-panel
    data-recording-path="{{ $workspaceData['recording_path'] ?? '' }}"
    data-recording-kind="{{ $workspaceData['recording_kind'] ?? 'audio' }}"
>
    @if ($recitationMode)
        <div>
            <p class="text-base font-extrabold text-es-ink">🎙 Enregistre ta récitation</p>
            <p class="text-sm text-es-muted mt-1">Ta voix sera sauvegardée et le professeur pourra l'écouter pour te corriger.</p>
        </div>
    @else
        <p class="text-sm font-semibold text-es-ink">Enregistre ta réponse orale</p>
        <p class="text-xs text-es-muted -mt-2">Autorise le micro (et la caméra pour la vidéo) quand le navigateur te le demande.</p>
    @endif

    @unless ($correctionMode)
        <div class="flex flex-wrap gap-2">
            <button type="button" class="es-btn es-btn-primary es-btn-sm oral-record-audio">
                {{ $recitationMode ? '🎤 Enregistrer ma voix' : '🎤 Audio' }}
            </button>
            @unless ($recitationMode)
                <button type="button" class="es-btn es-btn-secondary es-btn-sm oral-record-video">📹 Vidéo</button>
            @endunless
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
