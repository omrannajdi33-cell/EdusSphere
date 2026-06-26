<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Activity;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Point;
use App\Models\PointAction;
use App\Models\SchoolLevel;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $level = SchoolLevel::where('name', 'Primaire 3')->first();
        $teacher = User::where('email', 'prof@edusphere.fr')->first();
        $student = Student::whereHas('user', fn ($q) => $q->where('email', 'eleve1@edusphere.fr'))->first();

        $francais = Subject::where('name', 'Français')->first();
        $maths = Subject::where('name', 'Mathématiques')->first();

        if (! $francais || ! $maths || ! $level) {
            return;
        }

        $lecture = $francais->skills()->where('name', 'Lecture et compréhension')->first();
        $problemes = $maths->skills()->where('name', 'Résolution de problèmes')->first();

        if ($lecture) {
            Lesson::updateOrCreate(
                ['title' => 'Les contes de la lecture'],
                [
                    'subject_id' => $francais->id,
                    'skill_id' => $lecture->id,
                    'description' => 'Découvre comment lire et comprendre un petit conte.',
                    'school_level_id' => $level->id,
                    'estimated_duration_min' => 20,
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );

            Activity::updateOrCreate(
                ['title' => 'Quiz : comprendre un texte'],
                [
                    'subject_id' => $francais->id,
                    'skill_id' => $lecture->id,
                    'description' => 'Activité interactive sur la compréhension de lecture.',
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );

            $activity = Activity::where('title', 'Quiz : comprendre un texte')->first();
            if ($activity) {
                $studentIds = Student::query()
                    ->whereHas('user', fn ($q) => $q->where('status', 'active'))
                    ->pluck('id');
                if ($studentIds->isNotEmpty()) {
                    $activity->assignedStudents()->sync($studentIds);
                }
            }

            if ($activity && $activity->pages()->count() === 0) {
                $page = $activity->pages()->create([
                    'page_order' => 1,
                    'title' => 'Comprendre le texte',
                    'type' => 'interactive',
                    'content' => ['body' => 'Réponds aux questions suivantes. Tout se fait sur le site !'],
                ]);

                $page->questions()->create([
                    'type' => 'mcq',
                    'prompt' => 'De quoi parle surtout un conte ?',
                    'config' => [
                        'options' => [
                            ['text' => 'Une aventure imaginaire'],
                            ['text' => 'Une recette de cuisine'],
                            ['text' => 'Un horaire de train'],
                        ],
                        'correct' => 0,
                    ],
                    'display_order' => 1,
                ]);

                $page->questions()->create([
                    'type' => 'true_false',
                    'prompt' => 'Il faut lire lentement pour mieux comprendre.',
                    'config' => ['correct' => true],
                    'display_order' => 2,
                ]);

                $page->questions()->create([
                    'type' => 'choice_cards',
                    'prompt' => 'Quel outil t\'aide à comprendre un mot difficile ?',
                    'config' => [
                        'cards' => [
                            ['text' => 'Le dictionnaire', 'color' => '#4f46e5'],
                            ['text' => 'Une calculatrice', 'color' => '#f43f5e'],
                        ],
                        'correct' => 0,
                    ],
                    'display_order' => 3,
                ]);
            }
        }

        if ($problemes) {
            Exam::updateOrCreate(
                ['title' => 'Évaluation : problèmes du quotidien'],
                [
                    'subject_id' => $maths->id,
                    'skill_id' => $problemes->id,
                    'source_activity_id' => Activity::where('title', 'Quiz : comprendre un texte')->value('id'),
                    'description' => 'Résous des petits problèmes mathématiques.',
                    'duration_minutes' => 30,
                    'max_attempts' => 1,
                    'opens_at' => now()->subHour(),
                    'closes_at' => now()->addDays(7),
                    'status' => 'open',
                ],
            );
        }

        $this->seedDemoSchedule($francais, $maths);

        if ($teacher) {
            \App\Models\Announcement::updateOrCreate(
                ['title' => 'Bienvenue sur EduSphere !'],
                [
                    'body' => 'Consulte ton horaire, tes leçons et tes activités chaque jour. Bon courage !',
                    'target_type' => 'all',
                    'target_id' => null,
                    'published_at' => now(),
                    'created_by' => $teacher->id,
                ],
            );
        }

        if ($student && $teacher) {
            $participation = PointAction::where('name', 'Participation')->first();
            $excellent = PointAction::where('name', 'Excellent travail')->first();

            if ($participation) {
                Point::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'point_action_id' => $participation->id,
                        'note' => 'Participation en classe — démo',
                    ],
                    [
                        'awarded_by' => $teacher->id,
                        'value' => $participation->value,
                        'created_at' => now()->subDays(2),
                    ],
                );
            }

            if ($excellent) {
                Point::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'point_action_id' => $excellent->id,
                        'note' => 'Travail soigné — démo',
                    ],
                    [
                        'awarded_by' => $teacher->id,
                        'value' => $excellent->value,
                        'created_at' => now()->subDay(),
                    ],
                );
            }
        }
    }

    private function seedDemoSchedule(Subject $francais, Subject $maths): void
    {
        $slots = [
            [1, 1, $francais, 'Français — lecture'],
            [1, 3, $maths, 'Mathématiques'],
            [2, 2, $francais, 'Français — écriture'],
            [3, 1, $maths, 'Mathématiques'],
            [3, 4, $francais, 'Français — oral'],
            [4, 2, $maths, 'Mathématiques — problèmes'],
            [5, 1, $francais, 'Français — projet'],
        ];

        foreach ($slots as [$day, $period, $subject, $title]) {
            $periodDef = config('schedule.periods.'.$period, []);
            Schedule::updateOrCreate(
                [
                    'day_of_week' => $day,
                    'period_number' => $period,
                    'schedule_date' => null,
                ],
                [
                    'subject_id' => $subject->id,
                    'title' => $title,
                    'color' => $subject->color,
                    'starts_at' => $periodDef['starts_at'] ?? '08:30',
                    'ends_at' => $periodDef['ends_at'] ?? '09:45',
                ],
            );
        }
    }
}
