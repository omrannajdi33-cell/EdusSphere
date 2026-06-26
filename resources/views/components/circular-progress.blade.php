@props([
    'percent' => 0,
    'size' => 120,
    'stroke' => 10,
    'color' => '#7c3aed',
    'trackColor' => '#e7e5e4',
    'label' => null,
    'sublabel' => null,
])

@php
$p = min(100, max(0, (float) $percent));
$radius = ($size - $stroke) / 2;
$circumference = 2 * M_PI * $radius;
$offset = $circumference * (1 - $p / 100);
@endphp

<div {{ $attributes->merge(['class' => 'es-ring-chart flex flex-col items-center']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" role="img" aria-label="{{ $label ?? $p.'%' }}">
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="{{ $trackColor }}"
            stroke-width="{{ $stroke }}"
        />
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="{{ $stroke }}"
            stroke-linecap="round"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            transform="rotate(-90 {{ $size / 2 }} {{ $size / 2 }})"
        />
        <text x="50%" y="50%" text-anchor="middle" dy="0.35em" class="fill-es-ink text-xl font-black" style="font-size: {{ $size * 0.22 }}px;">
            {{ round($p) }}%
        </text>
    </svg>
    @if ($label)
        <p class="mt-3 text-sm font-extrabold text-es-ink text-center">{{ $label }}</p>
    @endif
    @if ($sublabel)
        <p class="text-xs font-semibold text-es-muted text-center mt-1">{{ $sublabel }}</p>
    @endif
</div>
