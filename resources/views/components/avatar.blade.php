@props([
    'name' => '?',
    'src' => null,
    'size' => 'md',
])

@php
$sizeClass = match ($size) {
    'sm' => 'es-avatar es-avatar-sm',
    'lg' => 'es-avatar es-avatar-lg',
    'xl' => 'es-avatar es-avatar-xl',
    default => 'es-avatar es-avatar-md',
};
$initial = mb_strtoupper(mb_substr($name, 0, 1));
@endphp

@if ($src)
    <img {{ $attributes->merge(['class' => "$sizeClass object-cover", 'style' => 'box-shadow: 0 0 0 3px white;', 'src' => $src, 'alt' => $name]) }}>
@else
    <span {{ $attributes->merge(['class' => $sizeClass]) }}>
        {{ $initial }}
    </span>
@endif
