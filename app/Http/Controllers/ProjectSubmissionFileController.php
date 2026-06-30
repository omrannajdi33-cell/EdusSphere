<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmissionFile;
use App\Support\PrivateStorage;
use Symfony\Component\HttpFoundation\Response;

class ProjectSubmissionFileController extends Controller
{
    public function __invoke(Project $project, ProjectSubmissionFile $submissionFile): Response
    {
        $submission = $submissionFile->submission;
        abort_unless($submission && $submission->project_id === $project->id, 404);

        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent()) {
            abort_unless($submission->student_id === $user->student?->id, 403);
        } elseif (! $user->isTeacher()) {
            abort(403);
        }

        abort_unless(PrivateStorage::exists($submissionFile->path), 404);

        return PrivateStorage::disk()->response(
            $submissionFile->path,
            $submissionFile->filename,
            ['Content-Type' => $submissionFile->mime_type ?: 'application/octet-stream'],
        );
    }
}
