<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Correction;
use App\Models\Progression;
use Illuminate\View\View;

class HomeworkController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        $homework = $student
            ? Activity::with(['subject', 'skill'])
                ->homework()
                ->assignedToStudent($student)
                ->orderByRaw('due_at IS NULL, due_at ASC')
                ->get()
            : collect();

        $progress = $student
            ? Progression::where('student_id', $student->id)->whereNotNull('activity_id')->get()->keyBy('activity_id')
            : collect();

        $corrections = $student
            ? Correction::query()
                ->where('student_id', $student->id)
                ->whereIn('activity_id', $homework->pluck('id'))
                ->get()
                ->keyBy('activity_id')
            : collect();

        $duringSchool = $homework->where('homework_slot', Activity::HOMEWORK_DURING_SCHOOL)->values();
        $afterSchool = $homework->where('homework_slot', Activity::HOMEWORK_AFTER_SCHOOL)->values();

        $pendingCount = $homework->filter(fn (Activity $a) => $a->isPendingForStudent($student))->count();

        return view('student.homework.index', [
            'activeNav' => 'homework',
            'duringSchool' => $duringSchool,
            'afterSchool' => $afterSchool,
            'progress' => $progress,
            'corrections' => $corrections,
            'pendingCount' => $pendingCount,
        ]);
    }
}
