<?php

namespace App\Providers;

use App\Models\Grade;
use App\Models\Point;
use App\Models\Student;
use App\Policies\GradePolicy;
use App\Policies\PointPolicy;
use App\Policies\StudentPolicy;
use App\Services\BehaviorPointService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('fr');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(Point::class, PointPolicy::class);

        View::composer('layouts.student', function ($view) {
            $student = auth()->user()?->student;
            $view->with('pointsTotal', $student
                ? app(BehaviorPointService::class)->totalFor($student)
                : 0);
            $view->with('scheduleTheme', app(\App\Services\StudentScheduleThemeService::class)->resolve(student: $student));
        });
    }
}
