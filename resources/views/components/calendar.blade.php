@props([
    'month' => null,
    'year' => null,
    'events' => [],
])

@php
use Carbon\Carbon;

$date = Carbon::create($year ?? now()->year, $month ?? now()->month, 1);
$monthLabel = $date->translatedFormat('F Y');
$daysInMonth = $date->daysInMonth;
$startDay = ($date->copy()->startOfMonth()->dayOfWeekIso - 1 + 7) % 7;
$today = now()->day;
$isCurrentMonth = $date->isSameMonth(now());
@endphp

<div {{ $attributes->merge(['class' => 'es-card p-5']) }}>
    <div class="mb-5 flex items-center justify-between">
        <h3 class="text-xl font-black capitalize text-es-ink">{{ $monthLabel }}</h3>
        <div class="flex gap-2">
            <button type="button" class="es-btn-secondary es-btn-sm !min-h-9 !px-3" aria-label="Mois précédent">←</button>
            <button type="button" class="es-btn-secondary es-btn-sm !min-h-9 !px-3" aria-label="Mois suivant">→</button>
        </div>
    </div>

    <div class="mb-3 grid grid-cols-7 gap-1.5 text-center text-sm font-black uppercase tracking-wide text-es-muted">
        @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dow)
            <span>{{ $dow }}</span>
        @endforeach
    </div>

    <div class="grid grid-cols-7 gap-1">
        @for ($i = 0; $i < $startDay; $i++)
            <span class="es-cal-day es-cal-day-muted"></span>
        @endfor

        @for ($day = 1; $day <= $daysInMonth; $day++)
            @php
                $hasEvent = in_array($day, $events, true);
                $isToday = $isCurrentMonth && $day === $today;
            @endphp
            <span @class([
                'es-cal-day relative',
                'es-cal-day-today' => $isToday,
                'es-cal-day-default' => ! $isToday,
            ])>
                {{ $day }}
                @if ($hasEvent)
                    <span class="absolute bottom-1 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-orange-400"></span>
                @endif
            </span>
        @endfor
    </div>
</div>
