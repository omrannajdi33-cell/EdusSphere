<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_real_activity_count_and_schedule_link(): void
    {
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);

        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = $subject->skills()->firstOrFail();

        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Léa',
            'last_name' => 'Test',
        ]);

        $activity = Activity::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'title' => 'Activité dashboard',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $activity->assignedStudents()->sync([$student->id]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Mon horaire')
            ->assertSee('Horaire complet')
            ->assertSee('1 disponible(s)')
            ->assertSee('Activité dashboard');
    }

    public function test_dashboard_shows_announcement(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        Announcement::create([
            'title' => 'Info importante',
            'body' => 'Message test',
            'target_type' => 'all',
            'published_at' => now(),
            'created_by' => $teacher->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Info importante');
    }
}
