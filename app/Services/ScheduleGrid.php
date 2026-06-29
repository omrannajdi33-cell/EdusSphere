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
        $displayDays = max(1, (int) config('schedule.week_display_days', 7));
        $weekEnd = $weekStart->copy()->addDays($displayDays - 1);

        $recurring = Schedule::query()
            ->with(['subject', 'activities', 'exams'])
            ->whereNull('schedule_date')
            ->get()
            ->groupBy(fn (Schedule $s) => $s->day_of_week.'-'.$s->period_number);

        $specific = Schedule::query()
            ->with(['subject', 'activities', 'exams'])
            ->whereBetween('schedule_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy(fn (Schedule $s) => $s->schedule_date->toDateString().'-'.$s->period_number);

        $days = [];
        for ($offset = 0; $offset < $displayDays; $offset++) {
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
                'is_weekend' => $date->dayOfWeekIso >= 6,
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
        $date = Carbon::parse($date);
        $dateKey = $date->toDateString();

        $specific = Schedule::query()
            ->with(['subject', 'activities', 'exams'])
            ->whereDate('schedule_date', $dateKey)
            ->get()
            ->keyBy('period_number');

        $recurring = Schedule::query()
            ->with(['subject', 'activities', 'exams'])
            ->whereNull('schedule_date')
            ->where('day_of_week', $date->dayOfWeekIso)
            ->get()
            ->keyBy('period_number');

        $periods = [];
        foreach (array_keys(config('schedule.periods', [])) as $periodNumber) {
            $slot = $specific->get($periodNumber) ?? $recurring->get($periodNumber);
            $periods[$periodNumber] = $slot ? $this->formatSlot($slot) : null;
        }

        return $periods;
    }

    public function hasCoursesOn(CarbonInterface $date): bool
    {
        return collect($this->forDay($date))->filter()->isNotEmpty();
    }

    /** @return array<string, mixed>|null */
    public function currentSlot(CarbonInterface $at): ?array
    {
        $time = $at->format('H:i:s');
        $periods = $this->forDay($at);

        foreach ($periods as $slot) {
            if (! $slot) {
                continue;
            }

            $starts = $this->normalizeTime((string) $slot['starts_at']);
            $ends = $this->normalizeTime((string) $slot['ends_at']);

            if ($time >= $starts && $time <= $ends) {
                return $slot;
            }
        }

        return null;
    }

    public function currentPeriodNumber(CarbonInterface $at): ?int
    {
        $slot = $this->currentSlot($at);

        return $slot['period_number'] ?? null;
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }

    /** @return list<int> */
    public function monthEventDays(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($this->hasCoursesOn($cursor)) {
                $days[] = $cursor->day;
            }
            $cursor->addDay();
        }

        return array_values(array_unique($days));
    }

    /** @return Collection<int, Schedule> */
    public function upcomingSpecificDates(?CarbonInterface $from = null, int $limit = 30): Collection
    {
        $from = Carbon::parse($from ?? now())->startOfDay();

        return Schedule::query()
            ->with(['subject', 'activities', 'exams'])
            ->whereNotNull('schedule_date')
            ->where('schedule_date', '>=', $from->toDateString())
            ->orderBy('schedule_date')
            ->orderBy('period_number')
            ->limit($limit)
            ->get();
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
            'grid_label' => $slot->gridLabel(),
            'color' => $slot->display_color,
            'subject_id' => $slot->subject_id,
            'subject' => $slot->subject?->name,
            'starts_at' => $slot->starts_at,
            'ends_at' => $slot->ends_at,
            'is_specific' => $slot->schedule_date !== null,
            'schedule_date' => $slot->schedule_date?->toDateString(),
            'day_of_week' => $slot->day_of_week,
            'period_number' => $slot->period_number,
            'materials' => $slot->materials,
            'plan' => $slot->plan,
            'uses_custom_time' => (bool) $slot->uses_custom_time,
            'time_label' => $slot->timeLabel(),
            'has_notes' => $slot->hasPlanningDetails(),
            'activity_ids' => $slot->relationLoaded('activities')
                ? $slot->activities->pluck('id')->all()
                : [],
            'exam_ids' => $slot->relationLoaded('exams')
                ? $slot->exams->pluck('id')->all()
                : [],
            'activities' => $slot->relationLoaded('activities')
                ? $slot->activities->map(fn ($a) => ['id' => $a->id, 'title' => $a->title])->all()
                : [],
            'exams' => $slot->relationLoaded('exams')
                ? $slot->exams->map(fn ($e) => ['id' => $e->id, 'title' => $e->title])->all()
                : [],
        ];
    }
}
