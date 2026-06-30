@php
    $photoStudent = $student ?? auth()->user()?->student;
    $photoPath = $progression?->result_photo_path ?? '';
    $photoUrl = ($photoPath && $photoStudent)
        ? route('activities.result-photo.show', [$activity, $photoStudent], absolute: false).'?t='.($progression?->updated_at?->timestamp ?? time())
        : null;
    $photoReadOnly = $readOnly ?? false;
@endphp

<div
    class="result-photo-panel rounded-2xl border border-sky-200 bg-sky-50/60 p-4"
    data-result-photo-panel
    data-result-photo-path="{{ $photoPath }}"
>
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-sky-800">📷 Photo du résultat</p>
            <p class="text-sm text-sky-900/80 mt-0.5">Prends une photo claire de ton travail terminé (feuille, cahier, produit…).</p>
        </div>
        @unless ($photoReadOnly)
            <button type="button" class="es-btn es-btn-primary es-btn-sm shrink-0" data-result-photo-take>
                Prendre une photo
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

    <div data-result-photo-preview @class(['hidden' => ! $photoUrl])>
        @if ($photoUrl)
            <img
                src="{{ $photoUrl }}"
                alt="Photo du résultat"
                class="w-full max-h-72 object-contain rounded-xl border border-stone-200 bg-white"
            >
        @else
            <img alt="Photo du résultat" class="w-full max-h-72 object-contain rounded-xl border border-stone-200 bg-white">
        @endif
    </div>

    <p data-result-photo-empty @class(['text-sm text-sky-800/70 italic', 'hidden' => (bool) $photoUrl])>
        Aucune photo pour l'instant. Utilise le bouton ci-dessus pour ouvrir l'appareil photo de l'iPad.
    </p>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <p class="text-xs font-semibold text-es-muted min-h-[1rem]" data-result-photo-status aria-live="polite"></p>
        @unless ($photoReadOnly)
            <button type="button" class="text-xs font-bold text-red-600 hidden" data-result-photo-remove>Supprimer la photo</button>
        @endunless
    </div>
</div>
