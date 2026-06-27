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
    <img {{ $attributes->merge(['class' => "$sizeClass object-cover shrink-0", 'src' => $src, 'alt' => $name, 'loading' => 'lazy', 'decoding' => 'async']) }}>
@else
    <span {{ $attributes->merge(['class' => $sizeClass]) }}>
        {{ $initial }}
    </span>
@endif
