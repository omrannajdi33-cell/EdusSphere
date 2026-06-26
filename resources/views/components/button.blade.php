@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
$classes = match ($variant) {
    'primary' => 'es-btn-primary',
    'secondary', 'ghost' => 'es-btn-secondary',
    'danger' => 'es-btn-danger',
    default => 'es-btn-primary',
};
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
