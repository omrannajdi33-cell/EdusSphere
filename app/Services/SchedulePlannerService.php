<?php

namespace App\Services;

use App\Models\Schedule;

class SchedulePlannerService
{
    /** @param  array<string, mixed>  $validated */
    public function sync(Schedule $schedule, array $validated): void
    {
        $schedule->activities()->sync($this->intIds($validated['activity_ids'] ?? []));
        $schedule->exams()->sync($this->intIds($validated['exam_ids'] ?? []));
        $schedule->projects()->sync($this->intIds($validated['project_ids'] ?? []));
        $schedule->notions()->sync($this->intIds($validated['notion_ids'] ?? []));
    }

    /** @param  array<int|string>  $ids  @return list<int> */
    private function intIds(array $ids): array
    {
        return collect($ids)->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
