@props([
    'type' => 'info',
    'title' => null,
])

@php
$styles = match ($type) {
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    default => 'bg-indigo-50 border-indigo-200 text-indigo-800',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border px-4 py-3 text-sm $styles", 'role' => 'alert']) }}>
    @if ($title)
        <p class="font-semibold mb-1">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
