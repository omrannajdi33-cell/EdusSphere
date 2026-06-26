<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\MediaFile;
use App\Services\LessonDocumentService;
use Illuminate\Support\Facades\Storage;

class LessonMediaController extends Controller
{
    public function __invoke(Lesson $lesson, MediaFile $media, LessonDocumentService $documents)
    {
        abort_unless($media->lesson_id === $lesson->id, 404);
        abort_unless(auth()->check(), 403);

        if (auth()->user()->isStudent()) {
            abort_unless($lesson->status === 'published', 404);
        }

        $path = $documents->viewerPath($media);

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path, $media->filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }
}
