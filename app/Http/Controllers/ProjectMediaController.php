<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\Project;
use App\Support\PrivateStorage;

class ProjectMediaController extends Controller
{
    public function __invoke(Project $project, MediaFile $media)
    {
        abort_unless($media->project_id === $project->id, 404);

        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent() && ! $project->isVisibleToStudent($user->student)) {
            abort(404);
        }

        if (! PrivateStorage::exists($media->path)) {
            abort(404);
        }

        return PrivateStorage::disk()->response($media->path, $media->filename);
    }
}
