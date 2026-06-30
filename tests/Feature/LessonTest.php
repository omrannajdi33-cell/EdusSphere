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

    public function test_teacher_can_add_external_links_and_student_sees_them(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.store'), [
                'title' => 'Les fractions',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'external_links' => [
                    ['label' => 'Khan Academy', 'url' => 'https://example.com/fractions'],
                    ['label' => '', 'url' => ''],
                ],
            ])
            ->assertRedirect();

        $lesson = Lesson::where('title', 'Les fractions')->firstOrFail();
        $lesson->update(['status' => 'published', 'published_at' => now()]);

        $this->assertSame('https://example.com/fractions', $lesson->externalLinksForDisplay()[0]['url']);

        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('student.lessons.show', $lesson))
            ->assertOk()
            ->assertSee('Liens à consulter')
            ->assertSee('Khan Academy')
            ->assertSee('https://example.com/fractions', false);
    }

    public function test_teacher_can_filter_lessons_by_search_and_category(): void
    {
        $this->seed(\Database\Seeders\SchoolLevelSeeder::class);
        $level = \App\Models\SchoolLevel::where('name', 'Primaire 5')->firstOrFail();

        Lesson::create([
            'school_level_id' => $level->id,
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => '📚 Lecture avancée',
            'category' => 'Lecture',
            'description' => 'Test',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Lesson::create([
            'school_level_id' => $level->id,
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => '🔢 Les fractions',
            'category' => 'Nombres',
            'description' => 'Test',
            'status' => 'published',
            'published_at' => now(),
        ]);

        config(['schedule.calendar_levels' => ['Primaire 5']]);

        $this->actingAs($this->teacher)
            ->get(route('admin.lessons.index', ['level' => $level->id, 'q' => 'fractions']))
            ->assertOk()
            ->assertSee('Les fractions')
            ->assertDontSee('Lecture avancée');

        $this->actingAs($this->teacher)
            ->get(route('admin.lessons.index', ['level' => $level->id, 'category' => 'Lecture']))
            ->assertOk()
            ->assertSee('Lecture avancée')
            ->assertDontSee('Les fractions');
    }
}
