@props(['state' => 'saved'])

@php
[$icon, $text, $class] = match ($state) {
    'saving' => ['…', 'Sauvegarde en cours…', 'text-amber-600'],
    'error' => ['⚠', 'Erreur de synchronisation', 'text-red-600'],
    default => ['✓', 'Sauvegardé', 'text-emerald-600'],
};
@endphp

<div {{ $attributes->merge(['class' => "inline-flex items-center gap-2 text-sm font-medium $class"]) }}>
    <span aria-hidden="true">{{ $icon }}</span>
    <span>{{ $text }}</span>
</div>
