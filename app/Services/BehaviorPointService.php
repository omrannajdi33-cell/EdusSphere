<?php

namespace App\Services;

use App\Models\Point;
use App\Models\PointAction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

class BehaviorPointService
{
    public function totalFor(Student $student): int
    {
        return (int) $student->points()->sum('value');
    }

    public function award(Student $student, PointAction $action, User $teacher, ?string $note = null): Point
    {
        return Point::create([
            'student_id' => $student->id,
            'point_action_id' => $action->id,
            'awarded_by' => $teacher->id,
            'value' => $action->value,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    /**
     * @return Collection<int, Point>
     */
    public function recentFor(Student $student, int $limit = 30): Collection
    {
        return $student->points()
            ->with(['pointAction', 'awardedBy'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
