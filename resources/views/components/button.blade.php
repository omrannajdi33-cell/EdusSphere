@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
$classes = match ($variant) {
    'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
    'secondary' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50',
    default => 'bg-indigo-600 text-white hover:bg-indigo-700',
};
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-2xl px-5 py-2.5 text-sm font-medium transition $classes"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-2xl px-5 py-2.5 text-sm font-medium transition $classes"]) }}>
        {{ $slot }}
    </button>
@endif
