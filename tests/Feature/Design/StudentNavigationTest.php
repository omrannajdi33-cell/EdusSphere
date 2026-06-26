<?php

namespace Tests\Feature\Design;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_access_all_nav_sections(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $routes = [
            'student.dashboard',
            'student.subjects.index',
            'student.lessons.index',
            'student.activities.index',
            'student.exams.index',
            'student.bulletin.index',
            'student.schedule.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_teacher_cannot_access_student_routes(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }
}
