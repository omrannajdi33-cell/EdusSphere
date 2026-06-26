<?php

namespace Tests\Feature\Auth;

use App\Models\Grade;
use App\Models\Point;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_view_own_grade(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $otherUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'A',
            'last_name' => 'B',
        ]);

        $otherStudent = Student::create([
            'user_id' => $otherUser->id,
            'first_name' => 'C',
            'last_name' => 'D',
        ]);

        $ownGrade = Grade::create([
            'student_id' => $student->id,
            'value' => 15,
            'type' => 'activity',
            'calculated_at' => now(),
        ]);

        $otherGrade = Grade::create([
            'student_id' => $otherStudent->id,
            'value' => 12,
            'type' => 'activity',
            'calculated_at' => now(),
        ]);

        $this->actingAs($studentUser);
        $this->assertTrue($studentUser->can('view', $ownGrade));
        $this->assertFalse($studentUser->can('view', $otherGrade));
        $this->assertFalse($studentUser->can('update', $ownGrade));
    }

    public function test_student_cannot_modify_points(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'A',
            'last_name' => 'B',
        ]);

        $this->actingAs($studentUser);
        $this->assertFalse($studentUser->can('create', Point::class));

        $this->actingAs($teacher);
        $this->assertTrue($teacher->can('create', Point::class));
    }
}
