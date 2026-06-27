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

        return view('student.points.index', [
            'activeNav' => 'points',
            'total' => $points->totalFor($student),
            'history' => $points->recentFor($student),
        ]);
    }
}
