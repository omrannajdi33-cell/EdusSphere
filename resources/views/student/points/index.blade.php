@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes points</h1>
        <p class="es-page-subtitle">Tes bons points et rappels à l'ordre</p>
    </div>

    <div class="es-behavior-student-hero mb-8">
        <p class="text-base font-extrabold text-es-muted uppercase tracking-wide">Total</p>
        <p @class([
            'es-points-value',
            'text-emerald-600' => $total >= 0,
            'text-red-600' => $total < 0,
        ])>
            {{ $total >= 0 ? '+' : '' }}{{ $total }}
        </p>
        <p class="text-sm font-bold text-es-muted mt-2">points comportement</p>
    </div>

    <x-card title="Historique">
        @if ($history->isEmpty())
            <p class="text-es-muted">Aucun point pour le moment. Continue comme ça !</p>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($history as $point)
                    @php
                        $positive = $point->value > 0;
                    @endphp
                    <li class="py-4 flex items-start gap-4">
                        <span @class([
                            'es-behavior-history-badge shrink-0',
                            'es-behavior-history-badge-good' => $positive,
                            'es-behavior-history-badge-bad' => ! $positive,
                        ])>
                            {{ $positive ? '+' : '' }}{{ $point->value }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold text-es-ink">{{ $point->pointAction?->name ?? 'Action' }}</p>
                            @if ($point->pointAction?->description)
                                <p class="text-sm text-es-muted mt-0.5">{{ $point->pointAction->description }}</p>
                            @endif
                            <p class="text-xs font-bold text-es-muted mt-2">
                                {{ $point->created_at?->translatedFormat('d M Y · H:i') }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
</div>
@endsection
