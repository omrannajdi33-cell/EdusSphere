<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementAudience
{
    public function visibleToStudent(?Student $student): Builder
    {
        return Announcement::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $q) use ($student) {
                $q->where('target_type', 'all');

                if ($student?->school_level_id) {
                    $q->orWhere(function (Builder $q2) use ($student) {
                        $q2->where('target_type', 'level')
                            ->where('target_id', $student->school_level_id);
                    });
                }

                if ($student) {
                    $q->orWhere(function (Builder $q2) use ($student) {
                        $q2->where('target_type', 'student')
                            ->where('target_id', $student->id);
                    });
                }
            })
            ->latest('published_at');
    }
}
