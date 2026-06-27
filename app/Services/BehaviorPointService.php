<?php

namespace App\Services;

use App\Models\Point;
use App\Models\PointAction;
use App\Models\PointRedemption;
use App\Models\PointReward;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BehaviorPointService
{
    public function totalFor(Student $student): int
    {
        $earned = (int) $student->points()->sum('value');
        $spent = (int) $student->pointRedemptions()->sum('cost');

        return $earned - $spent;
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

    public function redeem(Student $student, PointReward $reward): PointRedemption
    {
        if (! $reward->is_active) {
            throw ValidationException::withMessages([
                'reward' => 'Cette récompense n\'est plus disponible.',
            ]);
        }

        if ($this->totalFor($student) < $reward->cost) {
            throw ValidationException::withMessages([
                'reward' => 'Pas assez de points pour cette récompense.',
            ]);
        }

        return DB::transaction(function () use ($student, $reward) {
            $lockedTotal = $this->totalFor($student);

            if ($lockedTotal < $reward->cost) {
                throw ValidationException::withMessages([
                    'reward' => 'Pas assez de points pour cette récompense.',
                ]);
            }

            return PointRedemption::create([
                'student_id' => $student->id,
                'point_reward_id' => $reward->id,
                'cost' => $reward->cost,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * @return Collection<int, PointReward>
     */
    public function activeRewards(): Collection
    {
        return PointReward::active()->ordered()->get();
    }

    /**
     * @return Collection<int, array{type: string, label: string, value: int, description: ?string, at: \Illuminate\Support\Carbon}>
     */
    public function historyFor(Student $student, int $limit = 40): Collection
    {
        $points = $student->points()
            ->with(['pointAction', 'awardedBy'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Point $point) => [
                'type' => $point->value >= 0 ? 'good' : 'bad',
                'kind' => 'point',
                'label' => $point->pointAction?->name ?? 'Action',
                'description' => $point->pointAction?->description,
                'value' => $point->value,
                'at' => $point->created_at,
            ]);

        $redemptions = $student->pointRedemptions()
            ->with('pointReward')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (PointRedemption $redemption) => [
                'type' => 'reward',
                'kind' => 'redemption',
                'label' => $redemption->pointReward?->name ?? 'Récompense',
                'description' => $redemption->pointReward?->description,
                'value' => -$redemption->cost,
                'at' => $redemption->created_at,
            ]);

        return $points->concat($redemptions)->sortByDesc('at')->take($limit)->values();
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
