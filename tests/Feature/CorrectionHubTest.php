<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Correction;
use App\Models\Exam;
use App\Models\ExamAttempt;
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

class CorrectionHubTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);

        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Léo',
            'last_name' => 'Demo',
        ]);
    }

    public function test_corrections_hub_shows_activity_and_exam_tabs_on_one_page(): void
    {
        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = $subject->skills()->firstOrFail();

        $activity = Activity::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'title' => 'Lecture',
            'status' => 'published',
            'published_at' => now(),
        ]);

        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'P1',
            'type' => 'free_write',
            'content' => [],
        ]);

        Correction::create([
            'student_id' => $this->student->id,
            'activity_id' => $activity->id,
            'teacher_id' => $this->teacher->id,
            'status' => 'to_correct',
        ]);

        $period = ReportPeriod::create([
            'label' => 'T1',
            'school_year' => '2025-2026',
            'is_active' => true,
        ]);

        $exam = Exam::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'report_period_id' => $period->id,
            'weight_percent' => 50,
            'title' => 'Dictée',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
            'status' => 'open',
        ]);

        $page = ExamPage::create([
            'exam_id' => $exam->id,
            'page_order' => 1,
            'title' => 'Rédaction',
            'type' => 'interactive',
            'content' => [],
        ]);

        ExamQuestion::create([
            'exam_page_id' => $page->id,
            'type' => 'long_text',
            'prompt' => 'Raconte une histoire',
            'config' => [],
            'display_order' => 1,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
            'status' => 'submitted',
        ]);

        Correction::create([
            'student_id' => $this->student->id,
            'exam_attempt_id' => $attempt->id,
            'teacher_id' => $this->teacher->id,
            'status' => 'to_correct',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('admin.corrections.index'))
            ->assertOk()
            ->assertSee('Activités')
            ->assertSee('Examens')
            ->assertSee('Lecture')
            ->assertSee('Dictée')
            ->assertSee('x-data');
    }
}
