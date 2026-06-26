<?php

namespace App\Providers;

use App\Models\Grade;
use App\Models\Point;
use App\Models\Student;
use App\Policies\GradePolicy;
use App\Policies\PointPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(Point::class, PointPolicy::class);
    }
}
