@php
    $photoPaths = $progression?->resultPhotoPaths() ?? [];
    $photoReadOnly = $readOnly ?? false;
@endphp

<div
    class="result-photo-panel rounded-2xl border border-sky-200 bg-sky-50/60 p-4"
    data-result-photo-panel
    data-result-photos="@json($photoPaths)"
>
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-sky-800">📷 Photos du résultat</p>
            <p class="text-sm text-sky-900/80 mt-0.5">Prends une ou plusieurs photos claires de ton travail (feuilles, cahier, produit…).</p>
            <p class="text-xs font-semibold text-sky-800/70 mt-1" data-result-photo-count>
                @if (count($photoPaths) > 0)
                    {{ count($photoPaths) }} photo(s)
                @endif
            </p>
        </div>
        @unless ($photoReadOnly)
            <button type="button" class="es-btn es-btn-primary es-btn-sm shrink-0" data-result-photo-take>
                + Ajouter une photo
            </button>
        @endunless
    </div>

    <input
        type="file"
        class="hidden"
        data-result-photo-input
        accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
        capture="environment"
        @disabled($photoReadOnly)
    >

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 hidden" data-result-photo-gallery></div>

    <p data-result-photo-empty @class(['text-sm text-sky-800/70 italic', 'hidden' => count($photoPaths) > 0])>
        Aucune photo pour l'instant. Utilise le bouton ci-dessus pour ouvrir l'appareil photo de l'iPad. Tu peux en ajouter plusieurs.
    </p>

    <p class="text-xs font-semibold text-es-muted min-h-[1rem] mt-2" data-result-photo-status aria-live="polite"></p>
</div>
