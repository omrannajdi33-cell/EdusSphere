@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
])

@php
$percent = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label)
        <div class="flex justify-between text-sm text-slate-600 mb-1">
            <span>{{ $label }}</span>
            <span>{{ $percent }}%</span>
        </div>
    @endif
    <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
        <div class="h-full rounded-full bg-indigo-500 transition-all duration-500" style="width: {{ $percent }}%"></div>
    </div>
</div>
