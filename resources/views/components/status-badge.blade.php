@props([
    'status' => 'draft',
    'label' => null,
])

@php
[$class, $text] = match ($status) {
    'published' => ['es-badge es-badge-published', $label ?? 'Publié'],
    'submitted' => ['es-badge es-badge-submitted', $label ?? 'Soumis'],
    'in_progress' => ['es-badge es-badge-progress', $label ?? 'En cours'],
    'corrected' => ['es-badge es-badge-corrected', $label ?? 'Corrigé'],
    default => ['es-badge es-badge-draft', $label ?? 'Brouillon'],
};
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $text }}
</span>
