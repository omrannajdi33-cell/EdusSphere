<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\RedeemPointRewardRequest;
use App\Models\PointReward;
use App\Services\BehaviorPointService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PointRedemptionController extends Controller
{
    public function store(
        RedeemPointRewardRequest $request,
        BehaviorPointService $points,
        NotificationService $notifications,
    ): JsonResponse|RedirectResponse {
        $student = $request->user()->student;
        abort_unless($student, 403);

        $reward = PointReward::active()->findOrFail($request->integer('reward_id'));
        $redemption = $points->redeem($student, $reward);
        $total = $points->totalFor($student);

        $notifications->notifyTeachers('reward_redeemed', [
            'message' => "{$student->full_name} a utilisé {$reward->cost} pts pour « {$reward->name} »",
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'reward_name' => $reward->name,
            'reward_cost' => $reward->cost,
            'url' => route('admin.points.index', absolute: false),
        ]);

        $message = "Récompense « {$reward->name} » obtenue ! (-{$reward->cost} pts)";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'total' => $total,
            ]);
        }

        return back()->with('success', $message);
    }
}
