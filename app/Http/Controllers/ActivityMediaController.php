<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\MediaFile;
use App\Support\PrivateStorage;

class ActivityMediaController extends Controller
{
    public function __invoke(Activity $activity, MediaFile $media)
    {
        abort_unless($media->activity_id === $activity->id, 404);

        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->isStudent() && ! $activity->isPublished()) {
            abort(404);
        }

        if (! PrivateStorage::exists($media->path)) {
            abort(404);
        }

        return PrivateStorage::disk()->response($media->path, $media->filename);
    }
}
