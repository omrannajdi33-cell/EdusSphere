<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Correction;
use App\Models\Grade;
use App\Models\Question;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionWorkflowTest extends TestCase
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

    public function test_submit_creates_correction_and_teacher_can_finalize_with_grade(): void
    {
        $activity = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'QCM test',
            'status' => 'draft',
        ]);

        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Questions',
            'type' => 'interactive',
            'content' => ['body' => 'Réponds'],
        ]);

        $question = Question::create([
            'activity_page_id' => $page->id,
            'type' => 'mcq',
            'prompt' => '2+2 ?',
            'config' => ['options' => [['text' => '3'], ['text' => '4']], 'correct' => 1],
            'display_order' => 1,
        ]);

        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'responses' => [$question->id => '1'],
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertOk();

        $this->assertDatabaseHas('corrections', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'status' => 'to_correct',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('admin.corrections.index'))
            ->assertOk()
            ->assertSee('Léo Demo');

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.corrections.finalize', [$activity, $this->student]), [
                'score' => 85,
                'comment' => 'Très bien !',
            ])
            ->assertRedirect(route('admin.corrections.index'));

        $this->assertDatabaseHas('corrections', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'status' => 'validated',
            'score' => 85,
        ]);

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'type' => 'activity',
            'source_id' => $activity->id,
            'value' => 85,
        ]);

        $this->assertDatabaseHas('progressions', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'workflow_status' => 'corrected',
        ]);
    }

    public function test_teacher_can_return_copy_and_student_can_resubmit(): void
    {
        $activity = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Rédaction',
            'status' => 'draft',
        ]);

        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Page 1',
            'type' => 'free_write',
            'content' => ['body' => 'Écris'],
        ]);

        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertOk();

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.corrections.return', [$activity, $this->student]), [
                'comment' => 'Refais la conclusion.',
            ])
            ->assertRedirect(route('admin.activities.submissions', $activity));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->studentUser->id,
            'type' => 'activity_returned',
        ]);

        $this->assertDatabaseHas('progressions', [
            'workflow_status' => 'returned',
        ]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'canvas' => ['strokes' => [], 'notes' => 'Version 2'],
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertOk();

        $this->assertDatabaseHas('corrections', [
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'status' => 'to_correct',
        ]);
    }

    public function test_auto_score_suggestion_for_mcq(): void
    {
        $activity = Activity::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'title' => 'Auto',
            'status' => 'draft',
        ]);

        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Q',
            'type' => 'interactive',
            'content' => [],
        ]);

        $q1 = Question::create([
            'activity_page_id' => $page->id,
            'type' => 'mcq',
            'prompt' => 'A',
            'config' => ['options' => [['text' => 'x'], ['text' => 'y']], 'correct' => 0],
            'display_order' => 1,
        ]);

        $q2 = Question::create([
            'activity_page_id' => $page->id,
            'type' => 'true_false',
            'prompt' => 'B',
            'config' => ['correct' => true],
            'display_order' => 2,
        ]);

        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'responses' => [
                    $q1->id => '0',
                    $q2->id => 'true',
                ],
            ]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity));

        $this->actingAs($this->teacher)
            ->get(route('admin.activities.corrections.show', [$activity, $this->student]))
            ->assertOk()
            ->assertSee('100');
    }
}
