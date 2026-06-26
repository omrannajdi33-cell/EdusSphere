<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use RuntimeException;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $officialSubjects = collect(config('subjects.official', []))->keyBy('slug');
        $skillsBySlug = config('official_skills', []);

        foreach ($skillsBySlug as $slug => $skills) {
            $subjectConfig = $officialSubjects->get($slug);

            if (! $subjectConfig) {
                throw new RuntimeException("Matière inconnue pour le slug [{$slug}]");
            }

            $subject = Subject::where('name', $subjectConfig['name'])->first();

            if (! $subject) {
                throw new RuntimeException("Exécutez SubjectSeeder avant SkillSeeder (matière : {$subjectConfig['name']})");
            }

            foreach ($skills as $order => $skill) {
                Skill::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'name' => $skill['name'],
                    ],
                    [
                        'weight_percent' => $skill['weight'],
                        'display_order' => $order + 1,
                    ],
                );
            }

            if (! Skill::isValidSubjectTotal($subject->id)) {
                $total = Skill::subjectTotalWeight($subject->id);
                throw new RuntimeException(
                    "Pondération invalide pour {$subject->name} : {$total} % (attendu 100 %)"
                );
            }
        }
    }
}
