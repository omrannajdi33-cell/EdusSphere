@props([
    'status' => 'draft',
    'label' => null,
])

@php
[$classes, $text] = match ($status) {
    'published' => ['bg-emerald-100 text-emerald-700', $label ?? 'Publié'],
    'submitted' => ['bg-blue-100 text-blue-700', $label ?? 'Soumis'],
    'in_progress' => ['bg-amber-100 text-amber-700', $label ?? 'En cours'],
    'corrected' => ['bg-violet-100 text-violet-700', $label ?? 'Corrigé'],
    default => ['bg-slate-100 text-slate-600', $label ?? 'Brouillon'],
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}>
    {{ $text }}
</span>
