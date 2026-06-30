<?php

namespace Database\Seeders;

use App\Models\Notion;
use App\Models\NotionCategory;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Charge le référentiel officiel de notions (config/official_notions.php).
 *
 * Production : php artisan db:seed --class=OfficialNotionsSeeder
 * Idempotent — met à jour sans supprimer les notions personnalisées.
 */
class OfficialNotionsSeeder extends Seeder
{
    /** @var array<string, array{color: string, icon: string, display_order: int}> */
    private array $extraSubjects = [
        'Univers social' => ['color' => '#eab308', 'icon' => 'globe', 'display_order' => 9],
        'Anglais' => ['color' => '#6366f1', 'icon' => 'globe', 'display_order' => 10],
        'Éthique et culture religieuse' => ['color' => '#a855f7', 'icon' => 'star', 'display_order' => 11],
    ];

    public function run(): void
    {
        $catalogByLevel = config('official_notions', []);
        $categoryOrder = 0;
        $notionCount = 0;

        foreach ($catalogByLevel as $levelName => $catalog) {
            $level = SchoolLevel::where('name', $levelName)->first();

            if (! $level) {
                $this->command?->warn("Niveau ignoré (introuvable) : {$levelName}");

                continue;
            }

            foreach ($catalog as $subjectName => $categories) {
                $subject = $this->resolveSubject($subjectName);

                foreach ($categories as $categoryName => $meta) {
                    $categoryOrder++;
                    $skill = isset($meta['skill'])
                        ? Skill::query()
                            ->where('subject_id', $subject->id)
                            ->where('name', $meta['skill'])
                            ->first()
                        : null;

                    $category = NotionCategory::updateOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'school_level_id' => $level->id,
                            'name' => $categoryName,
                        ],
                        [
                            'skill_id' => $skill?->id,
                            'description' => $this->categoryDescription($subjectName, $categoryName),
                            'display_order' => $categoryOrder,
                        ],
                    );

                    foreach ($meta['notions'] as $index => $title) {
                        Notion::updateOrCreate(
                            [
                                'notion_category_id' => $category->id,
                                'title' => $title,
                            ],
                            [
                                'subject_id' => $subject->id,
                                'content' => $this->notionParagraph($title, $categoryName, $subjectName, $levelName),
                                'display_order' => $index + 1,
                            ],
                        );
                        $notionCount++;
                    }
                }
            }
        }

        $this->command?->info("Notions officielles : {$notionCount} notion(s) dans {$categoryOrder} catégorie(s).");
    }

    private function resolveSubject(string $name): Subject
    {
        $subject = Subject::where('name', $name)->first();

        if ($subject) {
            return $subject;
        }

        $extra = $this->extraSubjects[$name] ?? [
            'color' => '#64748b',
            'icon' => 'document',
            'display_order' => 99,
        ];

        return Subject::create([
            'name' => $name,
            'color' => $extra['color'],
            'icon' => $extra['icon'],
            'display_order' => $extra['display_order'],
        ]);
    }

    private function categoryDescription(string $subjectName, string $categoryName): string
    {
        if ($subjectName === 'Sciences') {
            return 'Science et technologie — '.$categoryName;
        }

        return $categoryName;
    }

    private function notionParagraph(string $title, string $categoryName, string $subjectName, string $levelName): string
    {
        $label = $subjectName === 'Sciences' ? 'Science et technologie' : $subjectName;

        return "Au {$levelName}, dans le cadre de {$label} ({$categoryName}), l'élève développe la notion « {$title} » prévue au programme québécois.";
    }
}
