<?php

namespace Tests\Feature;

use App\Models\Notion;
use App\Models\NotionCategory;
use App\Models\SchoolLevel;
use App\Models\Subject;
use Database\Seeders\OfficialNotionsSeeder;
use Database\Seeders\SchoolLevelSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialNotionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_loads_official_notion_catalog_by_level(): void
    {
        $this->seed([SubjectSeeder::class, SkillSeeder::class, SchoolLevelSeeder::class]);
        $this->seed(OfficialNotionsSeeder::class);

        $primaryTwo = SchoolLevel::where('name', 'Primaire 2')->firstOrFail();
        $primaryFive = SchoolLevel::where('name', 'Primaire 5')->firstOrFail();
        $francais = Subject::where('name', 'Français')->firstOrFail();

        $this->assertTrue(
            NotionCategory::where('subject_id', $francais->id)
                ->where('school_level_id', $primaryFive->id)
                ->where('name', 'Lecture — Compréhension de textes')
                ->exists()
        );

        $this->assertTrue(
            NotionCategory::where('subject_id', $francais->id)
                ->where('school_level_id', $primaryTwo->id)
                ->where('name', 'Lecture')
                ->exists()
        );

        $this->assertTrue(
            Notion::where('title', 'Reconnaître les lettres et leurs sons')
                ->where('subject_id', $francais->id)
                ->whereHas('category', fn ($q) => $q->where('school_level_id', $primaryTwo->id))
                ->exists()
        );

        $this->assertTrue(
            Notion::where('title', 'Faire des inférences')
                ->where('subject_id', $francais->id)
                ->whereHas('category', fn ($q) => $q->where('school_level_id', $primaryFive->id))
                ->exists()
        );

        $this->assertGreaterThan(400, Notion::count());

        $this->assertTrue(Subject::where('name', 'Anglais')->exists());
        $this->assertTrue(Subject::where('name', 'Univers social')->exists());

        $countAfterFirstRun = Notion::count();
        $this->seed(OfficialNotionsSeeder::class);
        $this->assertSame($countAfterFirstRun, Notion::count());
    }
}
