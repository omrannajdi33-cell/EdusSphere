@props([
    'name' => '?',
    'src' => null,
    'size' => 'md',
])

@php
$sizes = match ($size) {
    'sm' => 'w-8 h-8 text-sm',
    'lg' => 'w-16 h-16 text-xl',
    default => 'w-10 h-10 text-base',
};
$initial = mb_strtoupper(mb_substr($name, 0, 1));
@endphp

@if ($src)
    <img {{ $attributes->merge(['class' => "$sizes rounded-full object-cover ring-2 ring-white shadow-sm", 'src' => $src, 'alt' => $name]) }}>
@else
    <span {{ $attributes->merge(['class' => "$sizes inline-flex items-center justify-center rounded-full bg-indigo-600 text-white font-semibold shadow-sm"]) }}>
        {{ $initial }}
    </span>
@endif
