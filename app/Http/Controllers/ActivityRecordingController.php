<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityRecordingController extends Controller
{
    public function __invoke(Request $request, Activity $activity, Student $student): StreamedResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent()) {
            abort_unless($user->student?->id === $student->id, 403);
            abort_unless($activity->isVisibleToStudent($student), 404);
        } elseif (! $user->isTeacher()) {
            abort(403);
        }

        $path = (string) $request->query('path', '');
        $prefix = 'activities/'.$activity->id.'/students/'.$student->id.'/';

        abort_unless($path !== '' && str_starts_with($path, $prefix), 403);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
