<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        if ($user->isTeacher()) {
            return true;
        }

        return $user->isStudent() && $user->student?->id === $student->id;
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isTeacher() || ($user->isStudent() && $user->student?->id === $student->id);
    }
}
