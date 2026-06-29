<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentScheduleThemeService;
use Carbon\Carbon;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentScheduleThemeTest extends TestCase
{
    use RefreshDatabase;

    private Subject $geographie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->geographie = Subject::where('name', 'Géographie')->firstOrFail();
    }

    public function test_weekend_with_scheduled_course_uses_subject_theme(): void
    {
        $saturday = Carbon::parse('next saturday')->startOfDay();

        Schedule::create([
            'subject_id' => $this->geographie->id,
            'title' => 'Géographie — sortie',
            'color' => $this->geographie->color,
            'day_of_week' => 6,
            'period_number' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:15',
            'schedule_date' => $saturday->toDateString(),
        ]);

        Carbon::setTestNow($saturday->copy()->setTime(10, 30));

        $theme = app(StudentScheduleThemeService::class)->resolve();

        $this->assertSame('subject', $theme['mode']);
        $this->assertSame('geographie', $theme['slug']);

        Carbon::setTestNow();
    }

    public function test_weekend_returns_rest_theme(): void
    {
        Carbon::setTestNow(Carbon::parse('next saturday 10:00'));

        $theme = app(StudentScheduleThemeService::class)->resolve();

        $this->assertSame('weekend', $theme['mode']);
        $this->assertSame('weekend', $theme['slug']);
        $this->assertStringContainsString('reposer', strtolower($theme['greeting']));

        Carbon::setTestNow();
    }

    public function test_outside_school_hours_returns_default_theme(): void
    {
        Carbon::setTestNow(Carbon::parse('next monday 07:00'));

        $theme = app(StudentScheduleThemeService::class)->resolve();

        $this->assertSame('default', $theme['mode']);

        Carbon::setTestNow();
    }

    public function test_during_geography_class_returns_subject_theme(): void
    {
        Schedule::create([
            'subject_id' => $this->geographie->id,
            'title' => 'Géographie — cartes',
            'color' => $this->geographie->color,
            'day_of_week' => 1,
            'period_number' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:15',
        ]);

        Carbon::setTestNow(Carbon::parse('next monday 10:30'));

        $theme = app(StudentScheduleThemeService::class)->resolve();

        $this->assertSame('subject', $theme['mode']);
        $this->assertSame('geographie', $theme['slug']);
        $this->assertSame('Géographie', $theme['name']);
        $this->assertSame('globe', $theme['icon']);
        $this->assertStringContainsString('Géographie', $theme['greeting']);

        Carbon::setTestNow();
    }

    public function test_empty_period_returns_default_theme(): void
    {
        Carbon::setTestNow(Carbon::parse('next monday 10:30'));

        $theme = app(StudentScheduleThemeService::class)->resolve();

        $this->assertSame('default', $theme['mode']);

        Carbon::setTestNow();
    }

    public function test_student_layout_shows_weekend_banner(): void
    {
        Carbon::setTestNow(Carbon::parse('next saturday 11:00'));

        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Va te reposer')
            ->assertSee('es-theme-weekend', false);

        Carbon::setTestNow();
    }
}
