<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ScheduleGrid
{
    /** @return array{week_start: Carbon, week_end: Carbon, days: list<array>, period_defs: array<int, array>} */
    public function forWeek(CarbonInterface $reference): array
    {
        $weekStart = Carbon::parse($reference)->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(4);

        $recurring = Schedule::query()
            ->with('subject')
            ->whereNull('schedule_date')
            ->whereIn('day_of_week', config('schedule.school_days', [1, 2, 3, 4, 5]))
            ->get()
            ->groupBy(fn (Schedule $s) => $s->day_of_week.'-'.$s->period_number);

        $specific = Schedule::query()
            ->with('subject')
            ->whereBetween('schedule_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy(fn (Schedule $s) => $s->schedule_date->toDateString().'-'.$s->period_number);

        $days = [];
        for ($offset = 0; $offset < 5; $offset++) {
            $date = $weekStart->copy()->addDays($offset);
            $dateKey = $date->toDateString();
            $periods = [];

            foreach (array_keys(config('schedule.periods', [])) as $periodNumber) {
                $specificKey = $dateKey.'-'.$periodNumber;
                $recurringKey = $date->dayOfWeekIso.'-'.$periodNumber;
                $slot = $specific->get($specificKey)?->first()
                    ?? $recurring->get($recurringKey)?->first();

                $periods[$periodNumber] = $slot ? $this->formatSlot($slot) : null;
            }

            $days[] = [
                'date' => $date,
                'date_key' => $dateKey,
                'day_of_week' => $date->dayOfWeekIso,
                'label' => $date->translatedFormat('l j M'),
                'short_label' => $date->translatedFormat('D j'),
                'is_today' => $date->isToday(),
                'periods' => $periods,
            ];
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'period_defs' => config('schedule.periods', []),
        ];
    }

    /** @return list<array|null> */
    public function forDay(CarbonInterface $date): array
    {
        $week = $this->forWeek($date);
        $dateKey = Carbon::parse($date)->toDateString();

        foreach ($week['days'] as $day) {
            if ($day['date_key'] === $dateKey) {
                return $day['periods'];
            }
        }

        return array_fill_keys(array_keys(config('schedule.periods', [])), null);
    }

    /** @return list<int> */
    public function monthEventDays(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($cursor->dayOfWeekIso <= 5) {
                $periods = $this->forDay($cursor);
                if (collect($periods)->filter()->isNotEmpty()) {
                    $days[] = $cursor->day;
                }
            }
            $cursor->addDay();
        }

        return array_values(array_unique($days));
    }

    /** @return Collection<int, Schedule> */
    public function recurringTemplates(): Collection
    {
        return Schedule::query()
            ->with('subject')
            ->whereNull('schedule_date')
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();
    }

    /** @return array<string, mixed> */
    private function formatSlot(Schedule $slot): array
    {
        return [
            'id' => $slot->id,
            'title' => $slot->display_title,
            'color' => $slot->display_color,
            'subject_id' => $slot->subject_id,
            'subject' => $slot->subject?->name,
            'starts_at' => $slot->starts_at,
            'ends_at' => $slot->ends_at,
            'is_specific' => $slot->schedule_date !== null,
            'schedule_date' => $slot->schedule_date?->toDateString(),
            'day_of_week' => $slot->day_of_week,
            'period_number' => $slot->period_number,
        ];
    }
}
