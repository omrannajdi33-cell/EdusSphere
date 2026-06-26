<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        $lessons = Lesson::with(['subject', 'skill', 'schoolLevel'])
            ->where('status', 'published')
            ->when($student?->school_level_id, function ($query) use ($student) {
                $query->where(function ($q) use ($student) {
                    $q->whereNull('school_level_id')
                        ->orWhere('school_level_id', $student->school_level_id);
                });
            })
            ->latest('published_at')
            ->get();

        return view('student.lessons.index', [
            'activeNav' => 'lessons',
            'lessons' => $lessons,
        ]);
    }

    public function show(Lesson $lesson): View
    {
        abort_unless($lesson->status === 'published', 404);

        $lesson->load(['subject', 'skill', 'schoolLevel', 'mediaFiles']);

        $annotations = auth()->user()->student
            ? \App\Models\LessonAnnotation::query()
                ->where('student_id', auth()->user()->student->id)
                ->where('lesson_id', $lesson->id)
                ->get()
                ->keyBy('media_file_id')
            : collect();

        return view('student.lessons.show', [
            'activeNav' => 'lessons',
            'lesson' => $lesson,
            'annotations' => $annotations,
        ]);
    }
}
