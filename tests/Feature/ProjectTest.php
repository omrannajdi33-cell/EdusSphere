<?php

namespace Tests\Feature;

use App\Models\Correction;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
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

        $this->subject = Subject::where('name', 'Français')->firstOrFail();
        $this->skill = $this->subject->skills()->firstOrFail();
    }

    private function createPublishedProject(array $overrides = []): Project
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.projects.store'), [
                'title' => 'Mon projet de recherche',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'project_type' => 'research',
                'submission_format' => 'online',
                'require_sources' => '1',
                'require_bibliography' => '1',
                ...$overrides,
            ])
            ->assertRedirect();

        $project = Project::firstOrFail();

        $this->actingAs($this->teacher)
            ->put(route('admin.projects.update', $project), [
                'title' => $project->title,
                'subject_id' => $project->subject_id,
                'skill_id' => $project->skill_id,
                'project_type' => $project->project_type,
                'submission_format' => $project->submission_format,
                'instructions' => 'Rédige une recherche sur le thème choisi.',
                'require_sources' => '1',
                'require_bibliography' => '1',
                'next_step' => 3,
            ])
            ->assertRedirect();

        $this->actingAs($this->teacher)
            ->post(route('admin.projects.publish', $project), [
                'student_ids' => [$this->student->id],
            ])
            ->assertRedirect();

        return $project->fresh();
    }

    public function test_teacher_can_create_and_publish_project(): void
    {
        $project = $this->createPublishedProject();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'published',
            'title' => 'Mon projet de recherche',
        ]);

        $this->assertTrue($project->assignedStudents()->where('student_id', $this->student->id)->exists());
    }

    public function test_student_can_save_and_submit_project_with_sources(): void
    {
        $project = $this->createPublishedProject();

        $this->actingAs($this->studentUser)
            ->get(route('student.projects.work', $project))
            ->assertOk()
            ->assertSee('Consignes du professeur');

        $this->actingAs($this->studentUser)
            ->postJson(route('student.projects.save', $project), [
                'content' => 'Voici mon compte rendu complet.',
                'sources' => [
                    ['type' => 'website', 'title' => 'Wikipedia', 'author' => '', 'url' => 'https://example.com', 'notes' => ''],
                ],
                'bibliography' => [
                    ['type' => 'book', 'title' => 'Mon livre', 'author' => 'Dupont', 'year' => '2020', 'publisher' => 'Flammarion'],
                ],
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.projects.submit', $project))
            ->assertOk();

        $this->assertDatabaseHas('project_submissions', [
            'project_id' => $project->id,
            'student_id' => $this->student->id,
            'workflow_status' => 'submitted',
        ]);

        $this->assertDatabaseHas('corrections', [
            'student_id' => $this->student->id,
            'status' => 'to_correct',
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'project_submitted',
        ]);
    }

    public function test_teacher_can_finalize_project_correction(): void
    {
        $project = $this->createPublishedProject();

        ProjectSubmission::create([
            'project_id' => $project->id,
            'student_id' => $this->student->id,
            'workflow_status' => 'submitted',
            'content' => 'Travail soumis',
            'sources' => [['title' => 'Source 1']],
            'bibliography' => [['title' => 'Livre 1']],
            'submitted_at' => now(),
        ]);

        Correction::create([
            'student_id' => $this->student->id,
            'project_submission_id' => ProjectSubmission::first()->id,
            'teacher_id' => $this->teacher->id,
            'status' => 'to_correct',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('admin.projects.corrections.show', [$project, $this->student]))
            ->assertOk()
            ->assertSee('Travail rédigé');

        $this->actingAs($this->teacher)
            ->post(route('admin.projects.corrections.finalize', [$project, $this->student]), [
                'score' => 88,
                'comment' => 'Excellent travail',
            ])
            ->assertRedirect(route('admin.corrections.index'));

        $this->assertDatabaseHas('project_submissions', [
            'project_id' => $project->id,
            'workflow_status' => 'corrected',
        ]);
    }
}
