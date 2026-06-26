<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\GradeAggregator;
use Illuminate\View\View;

class PointsController extends Controller
{
    public function __invoke(GradeAggregator $grades): View
    {
        $student = auth()->user()->student;
        $stats = $student ? $grades->forStudent($student) : [
            'general' => null,
            'by_subject' => [],
            'activity_grades' => collect(),
        ];

        $activityTitles = Activity::query()
            ->whereIn('id', $stats['activity_grades']->pluck('source_id'))
            ->pluck('title', 'id');

        return view('student.points.index', [
            'activeNav' => 'points',
            'general' => $stats['general'],
            'bySubject' => $stats['by_subject'],
            'activityGrades' => $stats['activity_grades'],
            'activityTitles' => $activityTitles,
            'subjects' => \App\Models\Subject::ordered()->get()->keyBy('id'),
        ]);
    }
}
