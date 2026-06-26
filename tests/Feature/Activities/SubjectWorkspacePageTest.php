<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectWorkspacePageTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    }

    public function test_teacher_can_add_reading_comprehension_page(): void
    {
        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = $subject->skills()->firstOrFail();

        $activity = Activity::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'title' => 'Lecture',
            'status' => 'draft',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('admin.activities.pages.store', $activity), [
                'title' => 'Texte 1',
                'type' => 'reading_comprehension',
                'body' => 'Lis puis réponds.',
                'passage' => 'Il était une fois…',
            ])
            ->assertRedirect();

        $page = $activity->pages()->firstOrFail();
        $this->assertSame('reading_comprehension', $page->type);
        $this->assertSame('Il était une fois…', $page->content['passage']);
    }
}
