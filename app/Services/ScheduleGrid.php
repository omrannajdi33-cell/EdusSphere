<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Exam;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ScheduleGrid
{
    /** @return array{week_start: Carbon, week_end: Carbon, days: list<array>, period_defs: array<int, array>} */
    public function forWeek(CarbonInterface $reference, ?Student $student = null): array
    {
        $weekStart = Carbon::parse($reference)->startOfWeek(CarbonInterface::MONDAY);
        $displayDays = max(1, (int) config('schedule.week_display_days', 7));
        $weekEnd = $weekStart->copy()->addDays($displayDays - 1);

        $recurring = $this->baseQuery()
            ->whereNull('schedule_date')
            ->get()
            ->when($student, fn (Collection $rows) => $rows->filter(fn (Schedule $s) => $s->isVisibleToStudent($student)))
            ->groupBy(fn (Schedule $s) => $s->day_of_week.'-'.$s->period_number);

        $specific = $this->baseQuery()
            ->whereBetween('schedule_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->when($student, fn (Collection $rows) => $rows->filter(fn (Schedule $s) => $s->isVisibleToStudent($student)))
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

                $periods[$periodNumber] = $slot ? $this->formatSlot($slot, $student) : null;
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
    public function forDay(CarbonInterface $date, ?Student $student = null): array
    {
        $date = Carbon::parse($date);
        $dateKey = $date->toDateString();

        $specific = $this->baseQuery()
            ->whereDate('schedule_date', $dateKey)
            ->get()
            ->when($student, fn (Collection $rows) => $rows->filter(fn (Schedule $s) => $s->isVisibleToStudent($student)))
            ->keyBy('period_number');

        $recurring = $this->baseQuery()
            ->whereNull('schedule_date')
            ->where('day_of_week', $date->dayOfWeekIso)
            ->get()
            ->when($student, fn (Collection $rows) => $rows->filter(fn (Schedule $s) => $s->isVisibleToStudent($student)))
            ->keyBy('period_number');

        $periods = [];
        foreach (array_keys(config('schedule.periods', [])) as $periodNumber) {
            $slot = $specific->get($periodNumber) ?? $recurring->get($periodNumber);
            $periods[$periodNumber] = $slot ? $this->formatSlot($slot, $student) : null;
        }

        return $periods;
    }

    public function hasCoursesOn(CarbonInterface $date, ?Student $student = null): bool
    {
        return collect($this->forDay($date, $student))->filter()->isNotEmpty();
    }

    /** @return array<string, mixed>|null */
    public function currentSlot(CarbonInterface $at, ?Student $student = null): ?array
    {
        $time = $at->format('H:i:s');
        $periods = $this->forDay($at, $student);

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

    public function currentPeriodNumber(CarbonInterface $at, ?Student $student = null): ?int
    {
        $slot = $this->currentSlot($at, $student);

        return $slot['period_number'] ?? null;
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }

    /** @return list<int> */
    public function monthEventDays(int $year, int $month, ?Student $student = null): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($this->hasCoursesOn($cursor, $student)) {
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

        return $this->baseQuery()
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

    /** @return \Illuminate\Database\Eloquent\Builder<Schedule> */
    private function baseQuery()
    {
        return Schedule::query()->with([
            'subject',
            'activities',
            'exams',
            'projects',
            'notions.category',
            'targetedStudents',
            'studentItems',
        ]);
    }

    /** @return array<string, mixed> */
    private function formatSlot(Schedule $slot, ?Student $student = null): array
    {
        $personalItems = [];
        if ($student && $slot->relationLoaded('studentItems')) {
            $personalItems = $slot->studentItems
                ->where('student_id', $student->id)
                ->values()
                ->map(fn ($item) => $item->toDisplayArray())
                ->all();
        }

        $activities = $slot->relationLoaded('activities')
            ? $slot->activities
                ->when($student, fn (Collection $rows) => $rows->filter(fn (Activity $a) => $a->isVisibleToStudent($student)))
                ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'type' => 'activity'])
                ->values()
                ->all()
            : [];

        $exams = $slot->relationLoaded('exams')
            ? $slot->exams->map(fn ($e) => ['id' => $e->id, 'title' => $e->title, 'type' => 'exam'])->all()
            : [];

        $projects = $slot->relationLoaded('projects')
            ? $slot->projects->map(fn ($p) => ['id' => $p->id, 'title' => $p->title, 'type' => 'project'])->all()
            : [];

        if ($student && count($personalItems) > 0) {
            $activities = collect($personalItems)->where('type', 'activity')->values()->all();
            $exams = collect($personalItems)->where('type', 'exam')->values()->all();
            $projects = collect($personalItems)->where('type', 'project')->values()->all();
        }

        $studentItemsPayload = $slot->relationLoaded('studentItems')
            ? $slot->studentItems->map(fn ($item) => [
                'student_id' => $item->student_id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'notes' => $item->notes,
            ])->values()->all()
            : [];

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
            'activity_ids' => $slot->relationLoaded('activities') ? $slot->activities->pluck('id')->all() : [],
            'exam_ids' => $slot->relationLoaded('exams') ? $slot->exams->pluck('id')->all() : [],
            'project_ids' => $slot->relationLoaded('projects') ? $slot->projects->pluck('id')->all() : [],
            'notion_ids' => $slot->relationLoaded('notions') ? $slot->notions->pluck('id')->all() : [],
            'student_ids' => $slot->relationLoaded('targetedStudents') ? $slot->targetedStudents->pluck('id')->all() : [],
            'student_items' => $studentItemsPayload,
            'activities' => $activities,
            'exams' => $exams,
            'projects' => $projects,
            'notions' => $slot->relationLoaded('notions')
                ? $slot->notions->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'category' => $n->category?->name,
                ])->all()
                : [],
            'personal_items' => $personalItems,
        ];
    }
}
