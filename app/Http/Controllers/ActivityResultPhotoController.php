<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Progression;
use App\Models\Student;
use App\Support\PrivateStorage;
use Symfony\Component\HttpFoundation\Response;

class ActivityResultPhotoController extends Controller
{
    public function __invoke(Activity $activity, Student $student): Response
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent()) {
            abort_unless($user->student?->id === $student->id, 403);
            abort_unless($activity->isVisibleToStudent($student), 404);
        } elseif (! $user->isTeacher()) {
            abort(403);
        }

        $progression = Progression::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->first();

        $path = $progression?->result_photo_path;
        $prefix = 'activities/'.$activity->id.'/students/'.$student->id.'/';

        abort_unless($path && str_starts_with($path, $prefix), 404);
        abort_unless(PrivateStorage::exists($path), 404);

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'heic', 'heif' => 'image/heic',
            default => 'image/jpeg',
        };

        return PrivateStorage::disk()->response($path, 'resultat.'.pathinfo($path, PATHINFO_EXTENSION), [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
        ]);
    }
}
