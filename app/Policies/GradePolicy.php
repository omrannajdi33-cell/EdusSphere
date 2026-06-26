<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Grade $grade): bool
    {
        if ($user->isTeacher()) {
            return true;
        }

        return $user->isStudent() && $user->student?->id === $grade->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->isTeacher();
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->isTeacher();
    }
}
