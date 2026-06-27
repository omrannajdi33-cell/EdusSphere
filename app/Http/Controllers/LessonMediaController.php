<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\MediaFile;
use App\Services\LessonDocumentService;
use App\Support\PrivateStorage;

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

        if (! PrivateStorage::exists($path)) {
            abort(404, 'Fichier introuvable sur le serveur. Remettez le document en ligne depuis l’admin.');
        }

        return PrivateStorage::disk()->response($path, $media->filename, [
            'Content-Type' => $documents->viewerMimeType($media),
            'Content-Disposition' => 'inline',
        ]);
    }
}
