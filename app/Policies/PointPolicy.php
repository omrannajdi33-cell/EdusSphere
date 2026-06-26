<?php

namespace App\Policies;

use App\Models\Point;
use App\Models\User;

class PointPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Point $point): bool
    {
        if ($user->isTeacher()) {
            return true;
        }

        return $user->isStudent() && $user->student?->id === $point->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, Point $point): bool
    {
        return $user->isTeacher();
    }

    public function delete(User $user, Point $point): bool
    {
        return $user->isTeacher();
    }
}
