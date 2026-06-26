<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class ExamHandRaiseController extends Controller
{
    public function __invoke(ExamAttempt $attempt, NotificationService $notifications): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $attempt->student_id === $student->id, 403);
        abort_unless($attempt->status === 'in_progress', 422);

        $attempt->loadMissing('exam');

        $notifications->notifyTeachers('exam_hand_raise', [
            'student_name' => $student->full_name,
            'exam_title' => $attempt->exam->title,
            'attempt_id' => $attempt->id,
            'url' => route('admin.exams.attempts.correct', $attempt),
        ]);

        return response()->json(['raised' => true]);
    }
}
