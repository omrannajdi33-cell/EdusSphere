<?php

namespace Tests\Feature;

use App\Models\Notion;
use App\Models\NotionCategory;
use App\Models\Subject;
use Database\Seeders\OfficialNotionsSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialNotionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_loads_official_notion_catalog(): void
    {
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->seed(OfficialNotionsSeeder::class);

        $francais = Subject::where('name', 'Français')->firstOrFail();
        $this->assertTrue(
            NotionCategory::where('subject_id', $francais->id)->where('name', 'Lecture — Compréhension de textes')->exists()
        );

        $this->assertTrue(
            Notion::where('title', 'Faire des inférences')->where('subject_id', $francais->id)->exists()
        );

        $this->assertGreaterThan(200, Notion::count());

        $this->assertTrue(Subject::where('name', 'Anglais')->exists());
        $this->assertTrue(Subject::where('name', 'Univers social')->exists());

        $countAfterFirstRun = Notion::count();
        $this->seed(OfficialNotionsSeeder::class);
        $this->assertSame($countAfterFirstRun, Notion::count());
    }
}
