<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Subject;
use App\Services\LessonContentEnricher;
use App\Services\OfficialLessonSeedParser;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Importe les leçons textuelles depuis les fichiers sources (format « Lecon seed »).
 *
 * Production : php artisan db:seed --class=OfficialLessonsSeeder --force
 */
class OfficialLessonsSeeder extends Seeder
{
    public function run(): void
    {
        $parser = app(OfficialLessonSeedParser::class);
        $enricher = app(LessonContentEnricher::class);
        $sources = config('official_lessons.sources', []);
        $imported = 0;
        $skipped = 0;

        foreach ($sources as $source) {
            $path = $this->resolvePath($source['path'] ?? '');
            if (! is_readable($path) || filesize($path) === 0) {
                $this->command?->warn("Fichier ignoré (introuvable ou vide) : {$path}");

                continue;
            }

            $lessons = $parser->parse((string) file_get_contents($path));

            foreach ($lessons as $entry) {
                $subjectName = $parser->resolveSubjectForStorage($entry);
                $subject = Subject::where('name', $subjectName)->first();
                $level = SchoolLevel::where('name', $entry['level'])->first();

                if (! $subject || ! $level) {
                    $skipped++;

                    continue;
                }

                $skill = Skill::query()
                    ->where('subject_id', $subject->id)
                    ->where('name', $entry['skill'])
                    ->first();

                if (! $skill) {
                    $this->command?->warn("Compétence introuvable : {$entry['skill']} ({$subjectName}) — « {$entry['title']} »");
                    $skipped++;

                    continue;
                }

                $plainTitle = $entry['title'];
                $title = $enricher->enrichTitle($plainTitle, $subjectName, $entry['domain']);
                $description = $enricher->enrichDescription($entry['description'], $entry['domain']);
                $sourceRef = hash('sha256', implode('|', [
                    $level->id,
                    $subject->id,
                    $entry['domain'],
                    $this->plainTitle($plainTitle),
                ]));

                Lesson::updateOrCreate(
                    ['source_ref' => $sourceRef],
                    [
                        'school_level_id' => $level->id,
                        'subject_id' => $subject->id,
                        'category' => $entry['domain'],
                        'skill_id' => $skill->id,
                        'title' => $title,
                        'description' => $description,
                        'status' => 'published',
                        'published_at' => now(),
                        'estimated_duration_min' => 15,
                    ],
                );

                $imported++;
            }
        }

        $this->command?->info("Leçons officielles : {$imported} leçon(s) importée(s).");
        if ($skipped > 0) {
            $this->command?->warn("Ignorées : {$skipped}.");
        }
    }

    private function resolvePath(string $path): string
    {
        if ($path === '') {
            throw new RuntimeException('Chemin de leçon vide dans config/official_lessons.php');
        }

        if ($path[0] === DIRECTORY_SEPARATOR || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return base_path($path);
    }

    private function plainTitle(string $title): string
    {
        $title = trim(preg_replace('/^[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]+\s*/u', '', $title) ?? $title);
        $title = str_replace(['’', '‘', '‛'], "'", $title);

        return mb_strtolower($title);
    }
}
