<?php

namespace Tests\Feature;

use App\Models\Correction;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\ReportPeriod;
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

    private ReportPeriod $period;

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
        $this->period = ReportPeriod::create([
            'label' => 'Trimestre 1',
            'school_year' => '2025-2026',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function projectStepOnePayload(array $overrides = []): array
    {
        return [
            'title' => 'Mon projet de recherche',
            'subject_id' => $this->subject->id,
            'report_period_id' => $this->period->id,
            'weight_percent' => 30,
            'skill_ids' => [$this->skill->id],
            'project_type' => 'research',
            'submission_format' => 'online',
            'require_bibliography' => '1',
            ...$overrides,
        ];
    }

    private function createPublishedProject(array $overrides = []): Project
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.projects.store'), $this->projectStepOnePayload($overrides))
            ->assertRedirect();

        $project = Project::firstOrFail();

        $this->actingAs($this->teacher)
            ->put(route('admin.projects.update', $project), [
                ...$this->projectStepOnePayload([
                    'title' => $project->title,
                    'instructions' => 'Rédige une recherche sur le thème choisi.',
                    'next_step' => 3,
                ]),
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
            'weight_percent' => 30,
            'report_period_id' => $this->period->id,
        ]);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $this->skill->id,
        ]);

        $this->assertTrue($project->assignedStudents()->where('student_id', $this->student->id)->exists());
    }

    public function test_create_project_shows_validation_errors_when_skills_missing(): void
    {
        $this->actingAs($this->teacher)
            ->from(route('admin.projects.create'))
            ->post(route('admin.projects.store'), [
                'title' => 'Projet sans compétence',
                'subject_id' => $this->subject->id,
                'report_period_id' => $this->period->id,
                'weight_percent' => 25,
                'project_type' => 'research',
                'submission_format' => 'online',
                'require_bibliography' => '1',
            ])
            ->assertRedirect(route('admin.projects.create'))
            ->assertSessionHasErrors('skill_ids');

        $this->actingAs($this->teacher)
            ->get(route('admin.projects.create'))
            ->assertOk()
            ->assertSee('Impossible d')
            ->assertSee('Sélectionne au moins une compétence évaluée');
    }

    public function test_student_can_save_and_submit_project_with_bibliography(): void
    {
        $project = $this->createPublishedProject();

        $this->actingAs($this->studentUser)
            ->get(route('student.projects.work', $project))
            ->assertOk()
            ->assertSee('Consignes')
            ->assertSee('Bibliographie')
            ->assertDontSee('Sources');

        $this->actingAs($this->studentUser)
            ->postJson(route('student.projects.save', $project), [
                'content' => 'Voici mon compte rendu complet.',
                'research_notes' => "- Point 1\n- Point 2",
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
            ->assertSee('Rédaction');

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
