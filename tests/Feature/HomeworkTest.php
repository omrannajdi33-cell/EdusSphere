<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeworkTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $studentUser;

    private Student $student;

    private Subject $subject;

    private Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);

        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'first_name' => 'Lina',
            'last_name' => 'Demo',
        ]);

        $this->subject = Subject::where('name', 'Géographie')->firstOrFail();
        $this->skill = $this->subject->skills()->firstOrFail();
    }

    private function publishActivity(Activity $activity): void
    {
        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'type' => 'free_write',
            'title' => 'Étape 1',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.publish', $activity), [
                'student_ids' => [$this->student->id],
            ])
            ->assertRedirect();
    }

    public function test_teacher_can_mark_activity_as_after_school_homework(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.activities.store'), [
                'title' => 'Carte du Maroc',
                'description' => 'Colorie les régions.',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'device_type' => 'computer',
                'is_homework' => '1',
                'due_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'homework_slot' => Activity::HOMEWORK_AFTER_SCHOOL,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'title' => 'Carte du Maroc',
            'is_homework' => true,
            'homework_slot' => Activity::HOMEWORK_AFTER_SCHOOL,
        ]);
    }

    public function test_homework_requires_due_date_and_slot(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.activities.store'), [
                'title' => 'Sans date',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'device_type' => 'tablet',
                'is_homework' => '1',
            ])
            ->assertSessionHasErrors(['due_at', 'homework_slot']);
    }

    public function test_student_sees_homework_in_correct_sections(): void
    {
        $during = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Devoir en classe',
            'status' => 'draft',
            'is_homework' => true,
            'due_at' => now()->addDay(),
            'homework_slot' => Activity::HOMEWORK_DURING_SCHOOL,
        ]);

        $after = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Devoir à la maison',
            'status' => 'draft',
            'is_homework' => true,
            'due_at' => now()->addDays(2),
            'homework_slot' => Activity::HOMEWORK_AFTER_SCHOOL,
        ]);

        $this->publishActivity($during);
        $this->publishActivity($after);

        $this->actingAs($this->studentUser)
            ->get(route('student.homework.index'))
            ->assertOk()
            ->assertSee('Pendant l\'école', false)
            ->assertSee('Après l\'école', false)
            ->assertSee('Devoir en classe')
            ->assertSee('Devoir à la maison');

        $this->actingAs($this->studentUser)
            ->get(route('student.activities.index'))
            ->assertOk()
            ->assertDontSee('Devoir en classe')
            ->assertDontSee('Devoir à la maison');
    }

    public function test_regular_activity_stays_in_activities_not_homework(): void
    {
        $activity = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Activité en classe',
            'status' => 'draft',
            'is_homework' => false,
        ]);

        $this->publishActivity($activity);

        $this->actingAs($this->studentUser)
            ->get(route('student.activities.index'))
            ->assertOk()
            ->assertSee('Activité en classe');

        $this->actingAs($this->studentUser)
            ->get(route('student.homework.index'))
            ->assertOk()
            ->assertDontSee('Activité en classe');
    }
}
