@php
    $splitMode = $splitMode ?? false;
    $canvasMinH = $splitMode ? 'min-h-[360px]' : 'min-h-[420px]';
@endphp

@if ($page->isMathScroll())
    <div class="es-math-scroll-wrap overflow-y-auto rounded-2xl border border-stone-200 bg-white player-workspace {{ $splitMode ? 'h-full min-h-[360px]' : '' }}" style="{{ $splitMode ? '' : 'max-height: min(70vh, 640px);' }}">
        <div class="relative" style="min-height: {{ $scrollHeight }}px">
@else
    <div class="player-workspace relative rounded-2xl border border-stone-200 bg-white overflow-hidden {{ $canvasMinH }} {{ $splitMode ? 'h-full' : '' }}">
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
