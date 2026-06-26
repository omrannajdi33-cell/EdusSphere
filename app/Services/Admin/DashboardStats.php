<?php

namespace App\Services\Admin;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Correction;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;

class DashboardStats
{
    public static function get(): array
    {
        return Cache::remember('admin.dashboard.stats', 60, function () {
            return [
                'students_count' => Student::count(),
                'pending_activities' => Activity::where('status', 'draft')->count(),
                'active_exams' => Exam::where('status', 'open')->count(),
                'pending_corrections' => Correction::whereIn('status', ['submitted', 'to_correct'])->count(),
                'published_announcements' => Announcement::query()
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->count(),
                'recent_activities' => Activity::with('subject')->latest()->limit(5)->get(),
            ];
        });
    }

    public static function flush(): void
    {
        Cache::forget('admin.dashboard.stats');
    }
}
