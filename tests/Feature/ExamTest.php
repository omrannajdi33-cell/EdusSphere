<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamPage;
use App\Models\ExamQuestion;
use App\Models\ReportPeriod;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
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
            'first_name' => 'Léo',
            'last_name' => 'Demo',
        ]);

        $this->subject = Subject::where('name', 'Français')->firstOrFail();
        $this->skill = $this->subject->skills()->firstOrFail();
        $this->period = ReportPeriod::create([
            'label' => 'Trimestre 1',
            'school_year' => '2025-2026',
            'is_active' => true,
        ]);
    }

    public function test_teacher_can_create_exam_with_build_wizard(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.exams.create'))
            ->assertRedirect();

        $exam = Exam::firstOrFail();

        $this->actingAs($this->teacher)
            ->get(route('admin.exams.build', $exam))
            ->assertOk()
            ->assertSee('Informations & bulletin', false);

        $this->actingAs($this->teacher)
            ->put(route('admin.exams.update', $exam), [
                'title' => 'Contrôle lecture',
                'subject_id' => $this->subject->id,
                'skill_id' => $this->skill->id,
                'report_period_id' => $this->period->id,
                'weight_percent' => 50,
                'duration_minutes' => 45,
                'max_attempts' => 1,
                'opens_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'closes_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'status' => 'open',
            ])
            ->assertRedirect(route('admin.exams.build', $exam));

        $page = ExamPage::create([
            'exam_id' => $exam->id,
            'page_order' => 1,
            'title' => 'QCM',
            'type' => 'interactive',
            'content' => [],
        ]);

        ExamQuestion::create([
            'exam_page_id' => $page->id,
            'type' => 'mcq',
            'prompt' => '2+2 ?',
            'config' => ['options' => [['text' => '3'], ['text' => '4']], 'correct' => 1],
            'display_order' => 1,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.exams.index'))
            ->assertOk()
            ->assertSee('Contrôle lecture');
    }

    public function test_student_exam_updates_bulletin(): void
    {
        $exam = Exam::create([
            'subject_id' => $this->subject->id,
            'skill_id' => $this->skill->id,
            'report_period_id' => $this->period->id,
            'weight_percent' => 100,
            'title' => 'Examen final',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
            'status' => 'open',
        ]);

        $page = ExamPage::create([
            'exam_id' => $exam->id,
            'page_order' => 1,
            'title' => 'Partie A',
            'type' => 'interactive',
            'content' => [],
        ]);

        $question = ExamQuestion::create([
            'exam_page_id' => $page->id,
            'type' => 'mcq',
            'prompt' => 'Couleur du ciel ?',
            'config' => ['options' => [['text' => 'Vert'], ['text' => 'Bleu']], 'correct' => 1],
            'display_order' => 1,
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('student.exams.start', $exam))
            ->assertRedirect();

        $attempt = $exam->attempts()->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.exams.take', $attempt))
            ->assertOk()
            ->assertSee('Couleur du ciel', false);

        $this->actingAs($this->studentUser)
            ->postJson(route('student.exams.attempts.save', $attempt), [
                'page_id' => $page->id,
                'page_order' => 1,
                'total_pages' => 1,
                'responses' => [$question->id => '1'],
            ])
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->postJson(route('student.exams.submit', $attempt))
            ->assertOk();

        $this->actingAs($this->studentUser)
            ->get(route('student.exams.take', $attempt))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('info');

        $this->actingAs($this->studentUser)
            ->get(route('student.bulletin.index'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('100/100');
    }
}
