<?php

namespace Tests\Feature\Seeders;

use App\Models\Skill;
use App\Models\Subject;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_seeder_creates_eight_official_subjects(): void
    {
        $this->seed(SubjectSeeder::class);

        $this->assertDatabaseCount('subjects', 8);
        $this->assertDatabaseHas('subjects', ['name' => 'Français', 'color' => '#3b82f6']);
        $this->assertDatabaseHas('subjects', ['name' => 'Éducation physique', 'color' => '#f87171']);
    }

    public function test_school_levels_use_quebec_naming(): void
    {
        $this->seed(\Database\Seeders\SchoolLevelSeeder::class);

        $this->assertDatabaseHas('school_levels', ['name' => 'Primaire 1', 'display_order' => 1]);
        $this->assertDatabaseHas('school_levels', ['name' => 'Primaire 6', 'display_order' => 6]);
        $this->assertDatabaseMissing('school_levels', ['name' => 'CE2']);
    }

    public function test_skill_seeder_totals_one_hundred_percent_per_subject(): void
    {
        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);

        Subject::all()->each(function (Subject $subject) {
            $this->assertTrue(
                Skill::isValidSubjectTotal($subject->id),
                "Pondération invalide pour {$subject->name}"
            );
        });
    }

    public function test_francais_has_six_official_skills(): void
    {
        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);

        $francais = Subject::where('name', 'Français')->firstOrFail();

        $this->assertCount(6, $francais->skills);
        $this->assertEquals(30.0, (float) $francais->skills->firstWhere('name', 'Lecture et compréhension')->weight_percent);
    }

    public function test_demo_content_seeder_creates_sample_data(): void
    {
        $this->seed([
            \Database\Seeders\SchoolLevelSeeder::class,
            \Database\Seeders\PointActionSeeder::class,
            SubjectSeeder::class,
            SkillSeeder::class,
            \Database\Seeders\DemoUserSeeder::class,
            DemoContentSeeder::class,
        ]);

        $this->assertDatabaseHas('lessons', ['title' => 'Les contes de la lecture']);
        $this->assertDatabaseHas('activities', ['title' => 'Quiz : comprendre un texte']);
        $this->assertDatabaseHas('exams', ['title' => 'Évaluation : problèmes du quotidien']);
        $this->assertDatabaseCount('points', 2);
    }
}
