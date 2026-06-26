<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonAnnotation;
use App\Models\MediaFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonAnnotationController extends Controller
{
    public function save(Request $request, Lesson $lesson): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $lesson->status === 'published', 403);

        $data = $request->validate([
            'media_file_id' => ['required', 'exists:media_files,id'],
            'pages' => ['required', 'array'],
        ]);

        $media = MediaFile::query()
            ->where('id', $data['media_file_id'])
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        LessonAnnotation::updateOrCreate(
            [
                'student_id' => $student->id,
                'media_file_id' => $media->id,
            ],
            [
                'lesson_id' => $lesson->id,
                'content' => ['pages' => $data['pages']],
            ],
        );

        return response()->json(['saved' => true]);
    }
}
