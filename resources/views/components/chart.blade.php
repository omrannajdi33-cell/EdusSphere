@props([
    'items' => [],
    'max' => null,
    'color' => '#7c3aed',
    'showValues' => true,
])

@php
$maxValue = $max ?? (count($items) ? max(array_column($items, 'value')) : 1);
$maxValue = max(1, $maxValue);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-5']) }}>
    @foreach ($items as $item)
        @php
            $value = (float) ($item['value'] ?? 0);
            $percent = min(100, round(($value / $maxValue) * 100));
            $barColor = $item['color'] ?? $color;
        @endphp
        <div>
            <div class="mb-2.5 flex items-center justify-between text-base">
                <span class="font-black text-es-ink">{{ $item['label'] }}</span>
                @if ($showValues)
                    <span class="font-bold text-es-muted">{{ is_float($value) && fmod($value, 1) !== 0.0 ? number_format($value, 1) : (int) $value }}</span>
                @endif
            </div>
            <div class="es-chart-track">
                <div class="es-chart-bar" style="width: {{ $percent }}%; background-color: {{ $barColor }};"></div>
            </div>
        </div>
    @endforeach
</div>
