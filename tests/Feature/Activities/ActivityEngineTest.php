<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Progression;
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
        Storage::fake('private');
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
            ->assertSee('Validation');

        $this->actingAs($this->teacher)
            ->postJson(route('admin.activities.corrections.save', [$activity, $this->student]), [
                'page_id' => $page->id,
                'teacher_strokes' => [['tool' => 'pen', 'color' => '#dc2626', 'width' => 3, 'points' => [['x' => 10, 'y' => 10]]]],
            ])
            ->assertOk();
    }

    public function test_student_can_upload_oral_recording(): void
    {
        Storage::fake('private');

        $activity = $this->makeDraftActivity();
        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Oral',
            'type' => 'oral_recording',
            'content' => ['body' => 'Enregistre ta lecture'],
        ]);
        $activity->publishTo([$this->student->id]);

        $file = UploadedFile::fake()->create('audio.webm', 200, 'audio/webm');

        $response = $this->actingAs($this->studentUser)
            ->post(route('student.activities.recording.upload', $activity), [
                'page_id' => $page->id,
                'kind' => 'audio',
                'recording' => $file,
            ]);

        $response->assertOk()->assertJsonStructure(['path', 'kind', 'url']);
        $this->assertStringStartsWith('/activities/', $response->json('url'));
        $this->assertStringContainsString('/students/', $response->json('url'));

        $this->assertDatabaseHas('answers', [
            'student_id' => $this->student->id,
            'activity_page_id' => $page->id,
            'question_id' => null,
        ]);

        $answer = \App\Models\Answer::query()
            ->where('student_id', $this->student->id)
            ->where('activity_page_id', $page->id)
            ->whereNull('question_id')
            ->first();

        $this->assertSame($response->json('path'), $answer->content['workspace']['recording_path'] ?? null);
        $this->assertSame('audio', $answer->content['workspace']['recording_kind'] ?? null);
    }

    public function test_student_can_save_reading_workspace_notes(): void
    {
        $activity = $this->makeDraftActivity();
        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Lecture',
            'type' => 'reading_comprehension',
            'content' => ['passage' => 'Il était une fois…', 'body' => 'Lis le texte.'],
        ]);
        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'workspace' => ['text_hidden' => false, 'notes' => 'Ma compréhension'],
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $answer = \App\Models\Answer::query()
            ->where('student_id', $this->student->id)
            ->where('activity_page_id', $page->id)
            ->whereNull('question_id')
            ->first();

        $this->assertSame('Ma compréhension', $answer->content['workspace']['notes'] ?? null);
    }

    public function test_student_can_save_recitation_voice_with_passage_state(): void
    {
        Storage::fake('private');

        $activity = $this->makeDraftActivity();
        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Récitation',
            'type' => 'recitation',
            'content' => ['passage' => 'بِسْمِ اللَّهِ', 'body' => 'Récite.', 'rtl' => true],
        ]);
        $activity->publishTo([$this->student->id]);

        $file = UploadedFile::fake()->create('recitation.webm', 200, 'audio/webm');

        $upload = $this->actingAs($this->studentUser)
            ->post(route('student.activities.recording.upload', $activity), [
                'page_id' => $page->id,
                'kind' => 'audio',
                'recording' => $file,
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.save', $activity), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'workspace' => [
                    'text_hidden' => true,
                    'notes' => '',
                    'recording_path' => $upload->json('path'),
                    'recording_kind' => 'audio',
                ],
            ])
            ->assertOk();

        $answer = \App\Models\Answer::query()
            ->where('student_id', $this->student->id)
            ->where('activity_page_id', $page->id)
            ->whereNull('question_id')
            ->first();

        $this->assertSame($upload->json('path'), $answer->content['workspace']['recording_path'] ?? null);
        $this->assertTrue($answer->content['workspace']['text_hidden'] ?? false);
    }

    public function test_teacher_can_view_student_oral_recording(): void
    {
        Storage::fake('private');

        $activity = $this->makeDraftActivity();
        $activity->publishTo([$this->student->id]);

        $path = 'activities/'.$activity->id.'/students/'.$this->student->id.'/test-recording.webm';
        Storage::disk('private')->put($path, 'fake-webm-content');

        $this->actingAs($this->teacher)
            ->get(route('activities.recording.show', [$activity, $this->student], absolute: false).'?path='.urlencode($path))
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->get(route('activities.recording.show', [$activity, $this->student], absolute: false).'?path='.urlencode($path))
            ->assertOk();
    }

    public function test_teacher_correction_page_uses_shared_recording_route(): void
    {
        Storage::fake('private');

        $activity = $this->makeDraftActivity();
        $page = ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Oral',
            'type' => 'oral_recording',
            'content' => ['body' => 'Enregistre'],
        ]);
        $activity->publishTo([$this->student->id]);

        $path = 'activities/'.$activity->id.'/students/'.$this->student->id.'/clip.webm';
        Storage::disk('private')->put($path, 'fake-webm-content');

        \App\Models\Answer::create([
            'student_id' => $this->student->id,
            'activity_page_id' => $page->id,
            'content' => [
                'workspace' => [
                    'recording_path' => $path,
                    'recording_kind' => 'video',
                ],
            ],
        ]);

        \App\Models\Progression::create([
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'workflow_status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $expected = route('activities.recording.show', [$activity, $this->student], absolute: false).'?path='.urlencode($path);

        $this->actingAs($this->teacher)
            ->get(route('admin.activities.corrections.show', [$activity, $this->student]))
            ->assertOk()
            ->assertSee($expected, false);
    }

    public function test_student_cannot_play_draft_activity(): void
    {
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->studentUser)
            ->get(route('student.activities.play', $activity))
            ->assertNotFound();
    }

    public function test_device_type_is_inferred_from_activity_pages(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.activities.store'), [
                'title' => 'Activité auto matériel',
                'description' => 'Test',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
            ])
            ->assertRedirect();

        $activity = Activity::where('title', 'Activité auto matériel')->firstOrFail();
        $this->assertSame('computer', $activity->device_type);

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.pages.store', $activity), [
                'title' => 'Écriture libre',
                'type' => 'free_write',
                'body' => 'Consignes',
            ])
            ->assertRedirect();

        $activity->refresh();
        $this->assertSame('tablet', $activity->device_type);
    }

    public function test_config_has_eight_page_types_and_ten_question_types(): void
    {
        $this->assertCount(8, config('activity.page_types'));
        $this->assertCount(10, config('activity.question_types'));
    }

    public function test_student_can_upload_result_photo_and_teacher_can_view_it(): void
    {
        Storage::fake('private');

        $activity = $this->makeDraftActivity();
        $activity->update(['require_result_photo' => true]);
        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Travail',
            'type' => 'free_write',
            'content' => ['body' => 'Fais l\'exercice sur ta feuille.'],
        ]);
        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->post(route('student.activities.result-photo.upload', $activity), [
                'photo' => UploadedFile::fake()->create('resultat.jpg', 100, 'image/jpeg'),
            ])
            ->assertOk()
            ->assertJsonStructure(['path', 'url']);

        $progression = Progression::query()
            ->where('student_id', $this->student->id)
            ->where('activity_id', $activity->id)
            ->firstOrFail();

        $this->assertNotEmpty($progression->resultPhotoPaths());
        Storage::disk('private')->assertExists($progression->resultPhotoPaths()[0]);

        $this->actingAs($this->studentUser)
            ->post(route('student.activities.result-photo.upload', $activity), [
                'photo' => UploadedFile::fake()->create('resultat-2.jpg', 100, 'image/jpeg'),
            ])
            ->assertOk();

        $this->assertCount(2, $progression->fresh()->resultPhotoPaths());

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertOk();

        $this->actingAs($this->teacher)
            ->get(route('admin.activities.corrections.show', [$activity, $this->student]))
            ->assertOk()
            ->assertSee('Photos du résultat');

        $photoPath = $progression->fresh()->resultPhotoPaths()[0];

        $this->actingAs($this->teacher)
            ->get(route('activities.result-photo.show', [$activity, $this->student]).'?path='.urlencode($photoPath))
            ->assertOk();
    }

    public function test_submit_requires_result_photo_when_option_enabled(): void
    {
        $activity = $this->makeDraftActivity();
        $activity->update(['require_result_photo' => true]);
        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'Travail',
            'type' => 'free_write',
            'content' => ['body' => 'Consignes'],
        ]);
        $activity->publishTo([$this->student->id]);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.activities.submit', $activity))
            ->assertStatus(422)
            ->assertJson(['message' => 'Prends au moins une photo de ton résultat avant de soumettre.']);
    }

    public function test_teacher_can_delete_activity(): void
    {
        $activity = $this->makeDraftActivity();

        $this->actingAs($this->teacher)
            ->delete(route('admin.activities.destroy', $activity))
            ->assertRedirect(route('admin.activities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }
}
