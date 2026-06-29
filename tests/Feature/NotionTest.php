<?php

namespace Tests\Feature;

use App\Models\Notion;
use App\Models\NotionCategory;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotionTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubjectSeeder::class);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->subject = Subject::firstOrFail();
    }

    public function test_teacher_can_create_category_and_notion(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.notion-categories.store'), [
                'subject_id' => $this->subject->id,
                'name' => 'Grammaire',
            ])
            ->assertRedirect();

        $category = NotionCategory::firstOrFail();

        $this->actingAs($this->teacher)
            ->post(route('admin.notions.store'), [
                'notion_category_id' => $category->id,
                'title' => 'Le present de l indicatif',
                'content' => 'Conjuguer les verbes du 1er groupe au present.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notions', [
            'title' => 'Le present de l indicatif',
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_schedule_can_link_notion_and_student_plan(): void
    {
        $student = Student::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_STUDENT])->id,
            'first_name' => 'Noa',
            'last_name' => 'Test',
        ]);

        $category = NotionCategory::create([
            'subject_id' => $this->subject->id,
            'name' => 'Vocabulaire',
            'display_order' => 1,
        ]);

        $notion = Notion::create([
            'notion_category_id' => $category->id,
            'subject_id' => $this->subject->id,
            'title' => 'Les fractions',
            'content' => 'Comprendre numerateur et denominateur.',
            'display_order' => 1,
        ]);

        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), [
                'subject_id' => $this->subject->id,
                'title' => 'Maths',
                'period_number' => 1,
                'mode' => 'recurring',
                'day_of_week' => 1,
                'notion_ids' => [$notion->id],
                'student_ids' => [$student->id],
            ])
            ->assertRedirect();

        $schedule = Schedule::firstOrFail();
        $this->assertTrue($schedule->notions()->where('notion_id', $notion->id)->exists());
        $this->assertTrue($schedule->targetedStudents()->where('student_id', $student->id)->exists());
    }
}
