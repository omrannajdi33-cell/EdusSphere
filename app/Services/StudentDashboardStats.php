<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Progression;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

class StudentDashboardStats
{
    /** @return array<string, mixed> */
    public function for(?Student $student): array
    {
        if (! $student) {
            return $this->empty();
        }

        $activities = Activity::with(['subject', 'skill'])
            ->notHomework()
            ->where('status', 'published')
            ->whereHas('assignedStudents', fn ($q) => $q->where('students.id', $student->id))
            ->latest('published_at')
            ->get();

        $homework = Activity::query()
            ->homework()
            ->assignedToStudent($student)
            ->get();

        $homeworkPending = $homework->filter(fn (Activity $a) => $a->isPendingForStudent($student))->count();

        $progressions = Progression::query()
            ->where('student_id', $student->id)
            ->whereNotNull('activity_id')
            ->get()
            ->keyBy('activity_id');

        $lessons = Lesson::query()
            ->where('status', 'published')
            ->when($student->school_level_id, function ($query) use ($student) {
                $query->where(function ($q) use ($student) {
                    $q->whereNull('school_level_id')
                        ->orWhere('school_level_id', $student->school_level_id);
                });
            })
            ->get();

        $examsActive = Exam::query()
            ->where('status', '!=', 'draft')
            ->get()
            ->filter(fn (Exam $exam) => $exam->isOpenNow())
            ->count();

        $inProgress = $activities->filter(function (Activity $activity) use ($progressions) {
            $prog = $progressions->get($activity->id);

            return $prog
                && $prog->percent_complete > 0
                && ! in_array($prog->workflow_status, ['submitted', 'corrected'], true);
        });

        $toComplete = $activities->filter(function (Activity $activity) use ($progressions) {
            $prog = $progressions->get($activity->id);

            return ! $prog || ! in_array($prog->workflow_status, ['submitted', 'corrected'], true);
        });

        $globalProgress = $this->globalProgress($activities, $progressions);

        return [
            'activities_count' => $activities->count(),
            'homework_pending_count' => $homeworkPending,
            'lessons_count' => $lessons->count(),
            'exams_active_count' => $examsActive,
            'in_progress_count' => $inProgress->count(),
            'to_complete_count' => $toComplete->count(),
            'global_progress' => $globalProgress,
            'subjects' => $this->subjectCards($activities, $lessons, $progressions),
            'featured_activities' => $this->featuredActivities($activities, $progressions),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        return [
            'activities_count' => 0,
            'homework_pending_count' => 0,
            'lessons_count' => 0,
            'exams_active_count' => 0,
            'in_progress_count' => 0,
            'to_complete_count' => 0,
            'global_progress' => 0,
            'subjects' => Subject::ordered()->get()->map(fn (Subject $s) => [
                'subject' => $s->theme(),
                'activities_count' => 0,
                'lessons_count' => 0,
                'progress' => 0,
            ])->all(),
            'featured_activities' => collect(),
        ];
    }

    /** @param  Collection<int, Activity>  $activities */
    private function globalProgress(Collection $activities, Collection $progressions): int
    {
        if ($activities->isEmpty()) {
            return 0;
        }

        $total = $activities->sum(fn (Activity $activity) => (float) ($progressions->get($activity->id)?->percent_complete ?? 0));

        return (int) round($total / $activities->count());
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  Collection<int, Lesson>  $lessons
     * @param  Collection<int, Progression>  $progressions
     * @return list<array{subject: array, activities_count: int, lessons_count: int, progress: int}>
     */
    private function subjectCards(Collection $activities, Collection $lessons, Collection $progressions): array
    {
        return Subject::ordered()->get()->map(function (Subject $subject) use ($activities, $lessons, $progressions) {
            $subjectActivities = $activities->where('subject_id', $subject->id);
            $subjectLessons = $lessons->where('subject_id', $subject->id);

            $progress = $subjectActivities->isEmpty()
                ? 0
                : (int) round($subjectActivities->avg(
                    fn (Activity $a) => (float) ($progressions->get($a->id)?->percent_complete ?? 0)
                ));

            return [
                'subject' => $subject->theme(),
                'activities_count' => $subjectActivities->count(),
                'lessons_count' => $subjectLessons->count(),
                'progress' => $progress,
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  Collection<int, Progression>  $progressions
     * @return Collection<int, array{activity: Activity, progression: ?Progression}>
     */
    private function featuredActivities(Collection $activities, Collection $progressions): Collection
    {
        return $activities
            ->sortByDesc(function (Activity $activity) use ($progressions) {
                $prog = $progressions->get($activity->id);
                if ($prog && ! in_array($prog->workflow_status, ['submitted', 'corrected'], true) && $prog->percent_complete > 0) {
                    return 1000 + (float) $prog->percent_complete;
                }
                if (! $prog || $prog->percent_complete == 0) {
                    return 500;
                }

                return 100;
            })
            ->take(4)
            ->map(fn (Activity $activity) => [
                'activity' => $activity,
                'progression' => $progressions->get($activity->id),
            ])
            ->values();
    }
}
