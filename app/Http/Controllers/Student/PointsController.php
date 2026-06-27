<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\BehaviorPointService;
use Illuminate\View\View;

class PointsController extends Controller
{
    public function __invoke(BehaviorPointService $points): View
    {
        $student = auth()->user()->student;

        abort_unless($student, 403);

        $total = $points->totalFor($student);
        $rewards = $points->activeRewards();

        return view('student.points.index', [
            'activeNav' => 'points',
            'total' => $total,
            'history' => $points->historyFor($student),
            'rewards' => $rewards,
        ]);
    }
}
