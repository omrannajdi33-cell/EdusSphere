@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'color' => null,
])

@php
$percent = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label)
        <div class="flex justify-between text-base font-bold text-es-muted mb-2.5">
            <span>{{ $label }}</span>
            <span>{{ $percent }}%</span>
        </div>
    @endif
    <div class="es-progress-track">
        <div
            class="es-progress-fill"
            style="width: {{ $percent }}%;{{ $color ? " background: {$color};" : '' }}"
        ></div>
    </div>
</div>
