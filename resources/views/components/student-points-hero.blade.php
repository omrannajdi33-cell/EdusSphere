@props([
    'total' => 0,
    'compact' => false,
    'rewardsCount' => 0,
])

@php
    $positive = $total >= 0;
@endphp

<a
    href="{{ route('student.points.index') }}"
    {{ $attributes->class([
        'es-points-dashboard-hero group block',
        'es-points-dashboard-hero-compact' => $compact,
    ]) }}
    aria-label="Mes points : {{ $total >= 0 ? '+' : '' }}{{ $total }} points"
>
    <span class="es-points-deco es-points-deco-1" aria-hidden="true"></span>
    <span class="es-points-deco es-points-deco-2" aria-hidden="true"></span>
    <span class="es-points-deco es-points-deco-3" aria-hidden="true"></span>
    <span class="es-points-deco es-points-deco-star" aria-hidden="true">⭐</span>
    <span class="es-points-deco es-points-deco-spark" aria-hidden="true">✨</span>

    <div class="relative z-10">
        <p class="es-points-dashboard-kicker">Mes points comportement</p>
        <p @class([
            'es-points-dashboard-value',
            'es-points-dashboard-value-positive' => $positive,
            'es-points-dashboard-value-negative' => ! $positive,
        ])>
            {{ $total >= 0 ? '+' : '' }}{{ $total }}
        </p>
        <p class="es-points-dashboard-sub">
            @if ($rewardsCount > 0)
                {{ $rewardsCount }} récompense{{ $rewardsCount > 1 ? 's' : '' }} à débloquer
            @else
                Gagne des points et échange-les
            @endif
        </p>
        <span class="es-points-dashboard-cta">
            Voir mes récompenses
            <span class="es-points-dashboard-cta-arrow group-hover:translate-x-1 transition-transform inline-block">→</span>
        </span>
    </div>
</a>
