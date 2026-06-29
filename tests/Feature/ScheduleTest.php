<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleGrid;
use Carbon\Carbon;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Subject $francais;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->francais = Subject::where('name', 'Français')->firstOrFail();
    }

    public function test_teacher_can_view_schedule_page(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.schedules.index'))
            ->assertOk()
            ->assertSee('Horaire');
    }

    public function test_teacher_can_create_recurring_schedule_slot(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), [
                'subject_id' => $this->francais->id,
                'title' => 'Français',
                'period_number' => 1,
                'mode' => 'recurring',
                'day_of_week' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('schedules', [
            'subject_id' => $this->francais->id,
            'day_of_week' => 1,
            'period_number' => 1,
            'schedule_date' => null,
        ]);
    }

    public function test_schedule_grid_returns_slot_for_week(): void
    {
        Schedule::create([
            'subject_id' => $this->francais->id,
            'title' => 'Lecture',
            'color' => $this->francais->color,
            'day_of_week' => 1,
            'period_number' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:15',
        ]);

        $grid = app(ScheduleGrid::class)->forWeek(now()->startOfWeek());
        $monday = collect($grid['days'])->firstWhere('day_of_week', 1);

        $this->assertNotNull($monday['periods'][2]);
        $this->assertSame('Lecture', $monday['periods'][2]['title']);
    }

    public function test_teacher_can_create_specific_date_on_weekend(): void
    {
        $saturday = now()->next(Carbon::SATURDAY)->toDateString();

        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), [
                'subject_id' => $this->francais->id,
                'title' => 'Atelier samedi',
                'period_number' => 1,
                'mode' => 'specific',
                'schedule_date' => $saturday,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('schedules', [
            'title' => 'Atelier samedi',
        ]);
        $this->assertTrue(
            Schedule::where('title', 'Atelier samedi')->whereDate('schedule_date', $saturday)->exists()
        );

        $periods = app(ScheduleGrid::class)->forDay(Carbon::parse($saturday));
        $this->assertSame('Atelier samedi', $periods[1]['title']);
    }

    public function test_schedule_grid_shows_seven_days(): void
    {
        $grid = app(ScheduleGrid::class)->forWeek(now()->startOfWeek());

        $this->assertCount(7, $grid['days']);
        $this->assertSame(6, $grid['days'][5]['day_of_week']);
        $this->assertSame(7, $grid['days'][6]['day_of_week']);
    }

    public function test_student_can_view_schedule(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($studentUser)
            ->get(route('student.schedule.index', ['view' => 'week']))
            ->assertOk()
            ->assertSee('Mon horaire');
    }

    public function test_teacher_can_set_custom_time_and_link_activity(): void
    {
        $activity = \App\Models\Activity::create([
            'subject_id' => $this->francais->id,
            'skill_id' => $this->francais->skills()->firstOrFail()->id,
            'title' => 'Exercice lecture',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($this->teacher)
            ->post(route('admin.schedules.store'), [
                'subject_id' => $this->francais->id,
                'title' => 'Français spécial',
                'period_number' => 1,
                'mode' => 'specific',
                'schedule_date' => now()->addDay()->toDateString(),
                'use_custom_time' => '1',
                'starts_at' => '09:15',
                'ends_at' => '10:30',
                'activity_ids' => [$activity->id],
            ])
            ->assertRedirect();

        $schedule = Schedule::query()->where('title', 'Français spécial')->firstOrFail();

        $this->assertTrue($schedule->uses_custom_time);
        $this->assertStringStartsWith('09:15', (string) $schedule->starts_at);
        $this->assertStringStartsWith('10:30', (string) $schedule->ends_at);
        $this->assertTrue($schedule->activities()->where('activities.id', $activity->id)->exists());
    }
}
