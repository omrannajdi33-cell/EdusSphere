<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamPage;
use App\Models\ExamQuestion;
use App\Models\Report;
use App\Models\ReportPeriod;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\BulletinGeneratorService;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulletinGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Student $student;

    private ReportPeriod $periodT1;

    private ReportPeriod $periodT2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        Storage::fake('private');

        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->student = Student::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_STUDENT])->id,
            'first_name' => 'Léa',
            'last_name' => 'Test',
        ]);

        $year = '2025-2026';
        $this->periodT1 = ReportPeriod::create([
            'label' => 'Trimestre 1',
            'school_year' => $year,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->periodT2 = ReportPeriod::create([
            'label' => 'Trimestre 2',
            'school_year' => $year,
            'sort_order' => 2,
            'is_active' => false,
        ]);
    }

    public function test_teacher_can_generate_bulletin_with_skills_and_pdf(): void
    {
        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = Skill::where('subject_id', $subject->id)->firstOrFail();

        $exam = $this->createExam($subject, $skill, $this->periodT1, 'Contrôle lecture', 100);
        $this->submitExam($exam, 88.0);

        $this->actingAs($this->teacher)
            ->post(route('admin.reports.store'), [
                'report_period_id' => $this->periodT1->id,
                'student_id' => $this->student->id,
                'comment' => 'Bon travail',
            ])
            ->assertRedirect();

        $report = Report::firstOrFail();
        $this->assertNotNull($report->pdf_path);
        $this->assertSame('Trimestre 1', $report->period_label);
        $this->assertArrayHasKey('subjects', $report->payload);
        Storage::disk('private')->assertExists($report->pdf_path);

        $this->actingAs($this->teacher)
            ->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('Français')
            ->assertSee($skill->name);
    }

    public function test_trimestre_two_includes_trimestre_one_grades(): void
    {
        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = Skill::where('subject_id', $subject->id)->firstOrFail();

        $examT1 = $this->createExam($subject, $skill, $this->periodT1, 'Examen T1', 100);
        $this->submitExam($examT1, 80.0);

        $examT2 = $this->createExam($subject, $skill, $this->periodT2, 'Examen T2', 100);
        $this->submitExam($examT2, 90.0);

        $generator = app(BulletinGeneratorService::class);
        $report = $generator->generate($this->student, $this->periodT2, $this->teacher);

        $payload = $report->payload;
        $this->assertCount(2, $payload['included_periods']);
        $this->assertSame('Trimestre 1', $payload['included_periods'][0]['label']);
        $this->assertSame('Trimestre 2', $payload['included_periods'][1]['label']);

        $skillPayload = $payload['subjects'][0]['skills'][0];
        $this->assertSame('Examen T1', $skillPayload['periods'][0]['exams'][0]['title']);
        $this->assertSame('Examen T2', $skillPayload['periods'][1]['exams'][0]['title']);
    }

    private function createExam(Subject $subject, Skill $skill, ReportPeriod $period, string $title, float $weight): Exam
    {
        $exam = Exam::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'report_period_id' => $period->id,
            'weight_percent' => $weight,
            'title' => $title,
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'status' => 'open',
        ]);

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
            'prompt' => 'Test ?',
            'config' => ['options' => [['text' => 'A']], 'correct' => 0],
            'display_order' => 1,
        ]);

        return $exam;
    }

    private function submitExam(Exam $exam, float $score): void
    {
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subMinutes(20),
            'status' => 'in_progress',
            'attempts_remaining' => 0,
        ]);

        $attempt->update([
            'status' => 'corrected',
            'final_score' => $score,
            'finished_at' => now(),
        ]);
    }
}
