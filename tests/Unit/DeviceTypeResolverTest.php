<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Exam;
use App\Models\ExamPage;
use App\Models\Project;
use App\Services\DeviceTypeResolver;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTypeResolverTest extends TestCase
{
    use RefreshDatabase;

    private DeviceTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->resolver = app(DeviceTypeResolver::class);
    }

    public function test_interactive_page_resolves_to_computer(): void
    {
        $this->assertSame('computer', $this->resolver->resolveFromPageTypes(['interactive']));
    }

    public function test_pdf_worksheet_resolves_to_tablet(): void
    {
        $this->assertSame('tablet', $this->resolver->resolveFromPageTypes(['interactive', 'pdf_worksheet']));
    }

    public function test_oral_recording_resolves_to_tablet(): void
    {
        $this->assertSame('tablet', $this->resolver->resolveFromPageTypes(['oral_recording']));
    }

    public function test_activity_device_type_updates_when_page_added(): void
    {
        $subject = \App\Models\Subject::firstOrFail();
        $skill = $subject->skills()->firstOrFail();

        $activity = Activity::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'title' => 'Test auto device',
            'status' => 'draft',
            'device_type' => 'computer',
        ]);

        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 1,
            'title' => 'QCM',
            'type' => 'interactive',
            'content' => [],
        ]);

        $activity->refresh();
        $this->assertSame('computer', $activity->device_type);

        ActivityPage::create([
            'activity_id' => $activity->id,
            'page_order' => 2,
            'title' => 'Feuille',
            'type' => 'pdf_worksheet',
            'content' => [],
        ]);

        $activity->refresh();
        $this->assertSame('tablet', $activity->device_type);
    }

    public function test_creative_project_resolves_to_tablet(): void
    {
        $project = new Project(['project_type' => 'creative']);

        $this->assertSame('tablet', $this->resolver->forProject($project));
    }

    public function test_schedule_summary_formats_counts(): void
    {
        $summary = $this->resolver->formatScheduleSummary(['tablet' => 2, 'computer' => 1]);

        $this->assertSame('📱 2 · 💻 1', $summary);
    }
}
