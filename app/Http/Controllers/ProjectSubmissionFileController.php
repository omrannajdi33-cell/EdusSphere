<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\ProjectSubmissionFile;
use App\Support\PrivateStorage;

class ProjectSubmissionFileController extends Controller
{
    public function __invoke(Project $project, ProjectSubmissionFile $file)
    {
        $submission = $file->submission;
        abort_unless($submission->project_id === $project->id, 404);

        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent()) {
            abort_unless($submission->student_id === $user->student?->id, 403);
        }

        if (! PrivateStorage::exists($file->path)) {
            abort(404);
        }

        return PrivateStorage::disk()->response($file->path, $file->filename);
    }
}
