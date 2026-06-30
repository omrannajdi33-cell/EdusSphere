<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleGrid;
use Carbon\Carbon;
use Database\Seeders\SchoolLevelSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Subject $francais;

    private SchoolLevel $primaryFive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class, SchoolLevelSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->francais = Subject::where('name', 'Français')->firstOrFail();
        $this->primaryFive = SchoolLevel::where('name', 'Primaire 5')->firstOrFail();
    }

    private function schedulePayload(array $overrides = []): array
    {
        return array_merge([
            'school_level_id' => $this->primaryFive->id,
            'subject_id' => $this->francais->id,
            'period_number' => 1,
            'mode' => 'recurring',
            'day_of_week' => 1,
        ], $overrides);
    }

    public function test_teacher_can_view_schedule_page_with_level_tabs(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.schedules.index'))
            ->assertOk()
            ->assertSee('Horaire')
            ->assertSee('Primaire 2')
            ->assertSee('Primaire 5');
    }

    public function test_teacher_can_create_recurring_schedule_slot_for_level(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), $this->schedulePayload([
                'title' => 'Français',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('schedules', [
            'subject_id' => $this->francais->id,
            'school_level_id' => $this->primaryFive->id,
            'day_of_week' => 1,
            'period_number' => 1,
            'schedule_date' => null,
        ]);
    }

    public function test_schedule_grid_returns_slot_for_level(): void
    {
        Schedule::create([
            'subject_id' => $this->francais->id,
            'school_level_id' => $this->primaryFive->id,
            'title' => 'Lecture',
            'color' => $this->francais->color,
            'day_of_week' => 1,
            'period_number' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:15',
        ]);

        $grid = app(ScheduleGrid::class)->forWeek(now()->startOfWeek(), null, $this->primaryFive->id);
        $monday = collect($grid['days'])->firstWhere('day_of_week', 1);

        $this->assertNotNull($monday['periods'][2]);
        $this->assertSame('Lecture', $monday['periods'][2]['title']);
    }

    public function test_levels_have_separate_calendars(): void
    {
        $primaryTwo = SchoolLevel::where('name', 'Primaire 2')->firstOrFail();

        Schedule::create([
            'subject_id' => $this->francais->id,
            'school_level_id' => $primaryTwo->id,
            'title' => 'P2 Lecture',
            'color' => $this->francais->color,
            'day_of_week' => 1,
            'period_number' => 1,
            'starts_at' => '08:30',
            'ends_at' => '09:45',
        ]);

        Schedule::create([
            'subject_id' => $this->francais->id,
            'school_level_id' => $this->primaryFive->id,
            'title' => 'P5 Lecture',
            'color' => $this->francais->color,
            'day_of_week' => 1,
            'period_number' => 1,
            'starts_at' => '08:30',
            'ends_at' => '09:45',
        ]);

        $gridP2 = app(ScheduleGrid::class)->forWeek(now()->startOfWeek(), null, $primaryTwo->id);
        $gridP5 = app(ScheduleGrid::class)->forWeek(now()->startOfWeek(), null, $this->primaryFive->id);

        $this->assertSame('P2 Lecture', $gridP2['days'][0]['periods'][1]['title']);
        $this->assertSame('P5 Lecture', $gridP5['days'][0]['periods'][1]['title']);
    }

    public function test_teacher_can_create_specific_date_on_weekend(): void
    {
        $saturday = now()->next(Carbon::SATURDAY)->toDateString();

        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), $this->schedulePayload([
                'title' => 'Atelier samedi',
                'mode' => 'specific',
                'schedule_date' => $saturday,
            ]))
            ->assertRedirect();

        $periods = app(ScheduleGrid::class)->forDay(Carbon::parse($saturday), null, $this->primaryFive->id);
        $this->assertSame('Atelier samedi', $periods[1]['title']);
    }

    public function test_student_sees_only_their_level_schedule(): void
    {
        $primaryTwo = SchoolLevel::where('name', 'Primaire 2')->firstOrFail();

        Schedule::create([
            'subject_id' => $this->francais->id,
            'school_level_id' => $primaryTwo->id,
            'title' => 'Cours P2',
            'color' => $this->francais->color,
            'day_of_week' => now()->dayOfWeekIso,
            'period_number' => 1,
            'starts_at' => '08:30',
            'ends_at' => '09:45',
        ]);

        Schedule::create([
            'subject_id' => $this->francais->id,
            'school_level_id' => $this->primaryFive->id,
            'title' => 'Cours P5',
            'color' => $this->francais->color,
            'day_of_week' => now()->dayOfWeekIso,
            'period_number' => 1,
            'starts_at' => '08:30',
            'ends_at' => '09:45',
        ]);

        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Léo',
            'last_name' => 'Demo',
            'school_level_id' => $primaryTwo->id,
        ]);

        $periods = app(ScheduleGrid::class)->forDay(now(), $student);
        $this->assertSame('Cours P2', $periods[1]['title']);
    }

    public function test_teacher_can_link_content_once_per_slot(): void
    {
        $tabletActivity = $this->makePublishedActivity('Dessin fractions', 'tablet');
        $computerExam = $this->makeOpenExam('QCM lecture', 'computer');
        $scheduleDate = now()->addDay()->toDateString();

        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), $this->schedulePayload([
                'title' => 'Français spécial',
                'mode' => 'specific',
                'schedule_date' => $scheduleDate,
                'use_custom_time' => '1',
                'starts_at' => '09:15',
                'ends_at' => '10:30',
                'activity_ids' => [$tabletActivity->id],
                'exam_ids' => [$computerExam->id],
            ]))
            ->assertRedirect();

        $schedule = Schedule::query()->where('title', 'Français spécial')->firstOrFail();
        $this->assertTrue($schedule->uses_custom_time);
        $this->assertTrue($schedule->activities()->where('activities.id', $tabletActivity->id)->exists());

        $grid = app(ScheduleGrid::class)->forWeek(Carbon::parse($scheduleDate)->startOfWeek(), null, $this->primaryFive->id);
        $slot = collect($grid['days'])->firstWhere('date_key', $scheduleDate)['periods'][1];

        $this->assertSame(['tablet' => 1, 'computer' => 1], $slot['device_counts']);
        $this->assertStringContainsString('📱 1', $slot['device_summary']);
        $this->assertStringContainsString('💻 1', $slot['device_summary']);
    }

    private function makePublishedActivity(string $title, string $deviceType): \App\Models\Activity
    {
        return \App\Models\Activity::create([
            'subject_id' => $this->francais->id,
            'skill_id' => $this->francais->skills()->firstOrFail()->id,
            'title' => $title,
            'status' => 'published',
            'published_at' => now(),
            'device_type' => $deviceType,
        ]);
    }

    private function makeOpenExam(string $title, string $deviceType): \App\Models\Exam
    {
        return \App\Models\Exam::create([
            'subject_id' => $this->francais->id,
            'skill_id' => $this->francais->skills()->firstOrFail()->id,
            'title' => $title,
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'weight_percent' => 10,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
            'status' => 'open',
            'device_type' => $deviceType,
        ]);
    }
}
