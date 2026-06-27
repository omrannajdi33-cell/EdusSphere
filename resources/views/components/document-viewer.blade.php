@props([
    'fileUrl' => '',
    'pdfUrl' => '',
    'docKind' => 'pdf',
    'saveUrl' => '',
    'readOnly' => false,
    'initialAnnotations' => [],
    'mediaFileId' => null,
])

@php
    $pages = $initialAnnotations['pages'] ?? $initialAnnotations;
    $resolvedUrl = $fileUrl ?: $pdfUrl;
    $kind = strtolower($docKind ?: 'pdf');
@endphp

<div
    class="es-document-viewer"
    data-document-viewer
    data-file-url="{{ $resolvedUrl }}"
    data-doc-kind="{{ $kind }}"
    data-save-url="{{ $saveUrl }}"
    data-readonly="{{ $readOnly ? '1' : '0' }}"
    data-initial-annotations="@json($pages)"
    @if ($mediaFileId) data-media-id="{{ $mediaFileId }}" @endif
>
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3 p-2 bg-stone-100 rounded-xl">
        <div class="flex gap-2">
            <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-doc-prev aria-label="Page précédente">←</button>
            <span class="es-btn es-btn-secondary es-btn-sm pointer-events-none tabular-nums" data-doc-page-label>{{ $kind === 'pptx' ? 'Diapositive 1' : 'Page 1' }}</span>
            <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-doc-next aria-label="Page suivante">→</button>
        </div>
        @unless ($readOnly)
            <div class="flex items-center gap-2">
                <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-doc-clear>Effacer page</button>
                <span class="text-xs font-semibold text-es-muted" data-doc-save-status aria-live="polite"></span>
            </div>
        @endunless
    </div>

    <div class="overflow-auto max-h-[min(75vh,720px)] rounded-2xl border border-stone-200 bg-stone-100 p-3" data-doc-pages>
        <p class="text-sm text-es-muted p-4">Chargement du document…</p>
    </div>
</div>

@once
    @push('head')
        @vite('resources/js/document-viewer.js')
    @endpush
@endonce
