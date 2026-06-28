<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\AnnouncementAudience;
use App\Services\BehaviorPointService;
use App\Services\ScheduleGrid;
use App\Services\StudentDashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        AnnouncementAudience $announcements,
        ScheduleGrid $schedule,
        StudentDashboardStats $stats,
        BehaviorPointService $points,
    ): View {
        $student = auth()->user()->student;
        $firstName = $student?->first_name ?? explode(' ', auth()->user()->name)[0];
        $weekGrid = $schedule->forWeek(now());
        $todayPeriods = collect($weekGrid['days'])->firstWhere('is_today', true)['periods'] ?? [];
        $todayCourses = collect($todayPeriods)->filter()->values();

        $pointsTotal = $student ? $points->totalFor($student) : 0;
        $rewardsCount = $points->activeRewards()->count();
        $recentPoints = $student ? $points->historyFor($student, 4) : collect();

        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->limit(5)
            ->get();

        $unreadNotifications = Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('student.dashboard', [
            'activeNav' => 'home',
            'firstName' => $firstName,
            'announcements' => $announcements->visibleToStudent($student)->limit(5)->get(),
            'todayCourses' => $todayCourses,
            'stats' => $stats->for($student),
            'notifications' => $notifications,
            'unreadNotifications' => $unreadNotifications,
            'pointsTotal' => $pointsTotal,
            'rewardsCount' => $rewardsCount,
            'recentPoints' => $recentPoints,
        ]);
    }
}
