<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyTeachers(string $type, array $data): void
    {
        $now = now();

        User::query()
            ->where('role', User::ROLE_TEACHER)
            ->where('status', 'active')
            ->pluck('id')
            ->each(fn ($userId) => Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'data' => $data,
                'created_at' => $now,
            ]));
    }

    public function notifyStudent(User $user, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
            'created_at' => now(),
        ]);
    }
}
