<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Subject $subject;

    private Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->subject = Subject::where('name', 'Français')->firstOrFail();
        $this->skill = $this->subject->skills()->firstOrFail();
    }

    public function test_teacher_can_create_and_publish_lesson(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.store'), [
                'title' => 'Les contes',
                'description' => 'Introduction aux contes',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'estimated_duration_min' => 20,
            ])
            ->assertRedirect();

        $lesson = Lesson::where('title', 'Les contes')->firstOrFail();

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.publish', $lesson))
            ->assertRedirect();

        $lesson->refresh();
        $this->assertTrue($lesson->isPublished());
    }

    public function test_student_sees_published_lessons(): void
    {
        $lesson = Lesson::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Leçon visible',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('student.lessons.index'))
            ->assertOk()
            ->assertSee($lesson->title);
    }
}
