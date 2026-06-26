<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Question;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityEngineTest extends TestCase
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
            'first_name' => 'Léo',
            'last_name' => 'Demo',
        ]);

        $this->subject = Subject::where('name', 'Français')->firstOrFail();
        $this->skill = $this->subject->skills()->firstOrFail();
    }

    private function makeDraftActivity(): Activity
    {
        return Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Activité test',
            'description' => 'Description test',
            'status' => 'draft',
        ]);
    }

    public function test_teacher_can_create_interactive_step_with_questions(): void
    {
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.pages.store', $activity), [
                'title' => 'Questions',
                'type' => 'interactive',
                'body' => 'Réponds',
            ])
            ->assertRedirect();

        $page = $activity->pages()->firstOrFail();
        $this->assertSame('interactive', $page->type);

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.questions.store', [$activity, $page]), [
                'type' => 'multi_select',
                'prompt' => 'Coche les bonnes réponses',
                'options' => [['text' => 'A'], ['text' => 'B'], ['text' => 'C']],
                'correct_options' => [0, 2],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('questions', ['type' => 'multi_select']);
    }

    public function test_teacher_can_create_pdf_worksheet_step(): void
    {
        Storage::fake('local');
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.pages.store', $activity), [
                'title' => 'Feuille PDF',
                'type' => 'pdf_worksheet',
                'body' => 'Complète la feuille',
                'pdf' => UploadedFile::fake()->create('exercice.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $page = $activity->pages()->firstOrFail();
        $this->assertSame('pdf_worksheet', $page->type);
        $this->assertNotNull($page->mediaFile);
    }

    public function test_teacher_can_manage_and_publish_activity(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.activities.index'))
            ->assertOk()
            ->assertSee('Activités');

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.store'), [
                'title' => 'Mon quiz',
                'description' => 'Test',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
            ])
            ->assertRedirect(route('admin.activities.build', ['activity' => Activity::where('title', 'Mon quiz')->first(), 'step' => 2]));

        $activity = Activity::where('title', 'Mon quiz')->firstOrFail();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.pages.store', $activity), [
                'title' => 'Page 1',
                'type' => 'interactive',
                'body' => 'Consignes',
            ])
            ->assertRedirect();

        $page = $activity->pages()->firstOrFail();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.questions.store', [$activity, $page]), [
                'type' => 'mcq',
                'prompt' => 'Question test ?',
                'options' => [['text' => 'A'], ['text' => 'B']],
                'correct_option' => 0,
            ])
            ->assertRedirect();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.publish', $activity), [
                'student_ids' => [$this->student->id],
            ])
            ->assertRedirect(route('admin.activities.build', ['activity' => $activity, 'step' => 3]));

        $activity->refresh();
        $this->assertTrue($activity->isPublished());
        $this->assertTrue($activity->assignedStudents()->where('student_id', $this->student->id)->exists());
    }

    public function test_cannot_publish_without_selecting_students(): void
    {
        $activity = $this->makeDraftActivity();
        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Page 1',
            'type' => 'free_write',
            'content' => ['body' => 'Test'],
        ]);

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.publish', $activity), [
                'student_ids' => [],
            ])
            ->assertSessionHasErrors('student_ids');
    }

    public function test_unassigned_student_cannot_see_published_activity(): void
    {
        $activity = $this->makeDraftActivity();
        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Page 1',
            'type' => 'free_write',
            'content' => ['body' => 'Test'],
        ]);

        $otherStudentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        Student::create([
            'user_id' => $otherStudentUser->id,
            'first_name' => 'Sam',
            'last_name' => 'Autre',
        ]);

        $activity->publishTo([$this->student->id]);

        $this->actingAs($otherStudentUser)
            ->get(route('student.activities.index'))
            ->assertOk()
            ->assertDontSee($activity->title);

        $this->actingAs($otherStudentUser)
            ->get(route('student.activities.play', $activity))
            ->assertNotFound();
    }

    public function test_cannot_publish_activity_without_pages(): void
    {
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.publish', $activity), [
                'student_ids' => [$this->student->id],
            ])
            ->assertSessionHasErrors('publish');
    }

    public function test_student_can_play_submit_and_teacher_can_correct(): void
    {
        $activity = $this->makeDraftActivity();
        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Page 1',
            'type' => 'free_write',
            'content' => ['body' => 'Écris ici'],
        ]);
        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->get(route('student.activities.play', $activity))
            ->assertOk()
            ->assertSee('Page 1');

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'canvas' => ['strokes' => [], 'notes' => 'Ma réponse'],
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertOk();

        $this->assertDatabaseHas('progressions', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'workflow_status' => 'submitted',
        ]);

        $this->assertDatabaseHas('corrections', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'status' => 'to_correct',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('admin.activities.corrections.show', [$activity, $this->student]))
            ->assertOk()
            ->assertSee('Mode correction');

        $this->actingAs($this->teacher)
            ->postJson(route('admin.activities.corrections.save', [$activity, $this->student]), [
                'page_id' => $page->id,
                'teacher_strokes' => [['tool' => 'pen', 'color' => '#dc2626', 'width' => 3, 'points' => [['x' => 10, 'y' => 10]]]],
            ])
            ->assertOk();
    }

    public function test_student_cannot_play_draft_activity(): void
    {
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->studentUser)
            ->get(route('student.activities.play', $activity))
            ->assertNotFound();
    }

    public function test_config_has_eight_page_types_and_ten_question_types(): void
    {
        $this->assertCount(8, config('activity.page_types'));
        $this->assertCount(10, config('activity.question_types'));
    }
}
