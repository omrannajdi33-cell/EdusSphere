<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\SchoolLevel;
use Database\Seeders\OfficialLessonsSeeder;
use Database\Seeders\SchoolLevelSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialLessonsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_full_primaire_5_catalog(): void
    {
        $path = database_path('data/official_lessons_primaire_5.txt');
        $this->assertFileExists($path);
        $this->assertGreaterThan(50000, filesize($path));

        $this->seed([SubjectSeeder::class, SkillSeeder::class, SchoolLevelSeeder::class]);
        $this->seed(OfficialLessonsSeeder::class);

        $level = SchoolLevel::where('name', 'Primaire 5')->firstOrFail();

        $this->assertTrue(
            Lesson::where('school_level_id', $level->id)
                ->where('title', 'like', '%Compréhension de textes%')
                ->where('status', 'draft')
                ->exists()
        );

        $this->assertTrue(
            Lesson::where('school_level_id', $level->id)
                ->where('category', 'Opérations')
                ->exists()
        );

        $this->assertTrue(
            Lesson::where('school_level_id', $level->id)
                ->where('category', 'Géographie')
                ->exists()
        );

        $this->assertGreaterThan(120, Lesson::where('school_level_id', $level->id)->count());

        $sample = Lesson::where('title', 'like', '%Homophones%')->first();
        $this->assertNotNull($sample);
        $this->assertStringContainsString('📖', $sample->description);

        $countAfterFirstRun = Lesson::count();
        $this->seed(OfficialLessonsSeeder::class);
        $this->assertSame($countAfterFirstRun, Lesson::count());
    }
}
