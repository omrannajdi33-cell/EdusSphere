<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\ScheduleStudentItem;
use App\Models\Student;

class SchedulePlannerService
{
    /** @param  array<string, mixed>  $validated */
    public function sync(Schedule $schedule, array $validated): void
    {
        $schedule->activities()->sync($this->intIds($validated['activity_ids'] ?? []));
        $schedule->exams()->sync($this->intIds($validated['exam_ids'] ?? []));
        $schedule->projects()->sync($this->intIds($validated['project_ids'] ?? []));
        $schedule->notions()->sync($this->intIds($validated['notion_ids'] ?? []));
        $schedule->targetedStudents()->sync($this->intIds($validated['student_ids'] ?? []));

        $schedule->studentItems()->delete();

        foreach ($validated['student_items'] ?? [] as $index => $item) {
            if (empty($item['student_id']) || empty($item['item_type']) || empty($item['item_id'])) {
                continue;
            }

            ScheduleStudentItem::create([
                'schedule_id' => $schedule->id,
                'student_id' => (int) $item['student_id'],
                'item_type' => $item['item_type'],
                'item_id' => (int) $item['item_id'],
                'sort_order' => $index,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    /** @param  array<int|string>  $ids  @return list<int> */
    private function intIds(array $ids): array
    {
        return collect($ids)->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
