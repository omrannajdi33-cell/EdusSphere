<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Services\DeviceTypeResolver;

class ActivityPageDeviceTypeObserver
{
    public function __construct(
        private DeviceTypeResolver $resolver,
    ) {}

    public function saved(ActivityPage $page): void
    {
        $this->refresh($page->activity_id);
    }

    public function deleted(ActivityPage $page): void
    {
        $this->refresh($page->activity_id);
    }

    private function refresh(int $activityId): void
    {
        $activity = Activity::query()->with('pages')->find($activityId);

        if (! $activity) {
            return;
        }

        $activity->updateQuietly([
            'device_type' => $this->resolver->forActivity($activity),
        ]);
    }
}
