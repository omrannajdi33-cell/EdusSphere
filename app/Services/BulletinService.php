<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\ReportPeriod;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

class BulletinService
{
    /** @return array<int, array<string, mixed>> keyed by subject_id */
    public function subjectsForStudent(Student $student, ?ReportPeriod $period = null): array
    {
        $period ??= ReportPeriod::active();
        if (! $period) {
            return [];
        }

        $subjects = Subject::ordered()->get();
        $evaluations = $this->evaluationsForPeriod($period, $student);

        $result = [];

        foreach ($subjects as $subject) {
            $subjectEvaluations = $evaluations->get($subject->id, collect());
            $result[$subject->id] = $this->buildSubjectCard($subject, $subjectEvaluations);
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function subjectWeightsForPeriod(ReportPeriod $period, ?int $subjectId = null): array
    {
        $exams = Exam::query()
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->with('subject')
            ->get();

        $projects = Project::query()
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->with('subject')
            ->get();

        $groupedExams = $exams->groupBy('subject_id');
        $groupedProjects = $projects->groupBy('subject_id');
        $cards = [];

        foreach (Subject::ordered()->get() as $subject) {
            if ($subjectId && $subject->id !== $subjectId) {
                continue;
            }

            $subjectExams = $groupedExams->get($subject->id, collect());
            $subjectProjects = $groupedProjects->get($subject->id, collect());
            $totalWeight = round($subjectExams->sum('weight_percent') + $subjectProjects->sum('weight_percent'), 2);

            $cards[$subject->id] = [
                'subject' => $subject,
                'exams' => $subjectExams,
                'projects' => $subjectProjects,
                'total_weight' => $totalWeight,
                'missing_weight' => max(0, round(100 - $totalWeight, 2)),
                'is_complete' => abs(100 - $totalWeight) < 0.01,
            ];
        }

        return $cards;
    }

    /** @param Collection<int, array<string, mixed>> $evaluations */
    private function buildSubjectCard(Subject $subject, Collection $evaluations): array
    {
        $totalWeight = round($evaluations->sum('weight'), 2);
        $completedWeight = 0.0;
        $earnedPoints = 0.0;
        $details = [];

        foreach ($evaluations as $evaluation) {
            $score = $evaluation['score'];
            $weight = (float) $evaluation['weight'];

            if ($score !== null && $weight > 0) {
                $completedWeight += $weight;
                $earnedPoints += $score * ($weight / 100);
            }

            $details[] = [
                'evaluation' => $evaluation,
                'weight' => $weight,
                'score' => $score,
                'done' => $score !== null,
            ];
        }

        $completedWeight = round($completedWeight, 2);
        $missingForStudent = max(0, round(100 - $completedWeight, 2));
        $missingStructure = max(0, round(100 - $totalWeight, 2));

        $provisionalGrade = $completedWeight > 0
            ? round($earnedPoints / ($completedWeight / 100), 2)
            : null;

        $finalGrade = ($totalWeight >= 99.99 && $completedWeight >= 99.99)
            ? $provisionalGrade
            : null;

        return [
            'subject' => $subject,
            'total_weight' => $totalWeight,
            'completed_weight' => $completedWeight,
            'missing_weight' => $missingForStudent,
            'missing_structure' => $missingStructure,
            'progress_percent' => $completedWeight,
            'provisional_grade' => $provisionalGrade,
            'final_grade' => $finalGrade,
            'evaluations' => $details,
            'exams' => $details,
            'is_bulletin_complete' => $totalWeight >= 99.99 && $completedWeight >= 99.99,
        ];
    }

    /** @return Collection<int, Collection<int, array<string, mixed>>> */
    private function evaluationsForPeriod(ReportPeriod $period, Student $student): Collection
    {
        $exams = Exam::query()
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->get();

        $projects = Project::query()
            ->with('skills')
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->get();

        $attempts = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->whereIn('status', ['submitted', 'corrected'])
            ->whereNotNull('final_score')
            ->get()
            ->groupBy('exam_id');

        $projectScores = ProjectSubmission::query()
            ->with('correction')
            ->where('student_id', $student->id)
            ->whereIn('project_id', $projects->pluck('id'))
            ->where('workflow_status', 'corrected')
            ->get()
            ->mapWithKeys(function (ProjectSubmission $submission) {
                $score = $submission->correction?->score;

                return [$submission->project_id => $score !== null ? (float) $score : null];
            });

        $rows = collect();

        foreach ($exams as $exam) {
            $best = $attempts->get($exam->id)?->sortByDesc('final_score')->first();
            $score = $best?->final_score !== null ? (float) $best->final_score : null;

            $rows->push([
                'subject_id' => $exam->subject_id,
                'id' => $exam->id,
                'title' => $exam->title,
                'type' => 'exam',
                'weight' => (float) $exam->weight_percent,
                'score' => $score,
                'model' => $exam,
            ]);
        }

        foreach ($projects as $project) {
            $score = $projectScores->get($project->id);
            $skills = $project->skills;

            if ($skills->isEmpty() && $project->skill_id) {
                $skills = collect([(object) ['id' => $project->skill_id, 'pivot' => (object) ['weight_percent' => 100]]]);
            }

            foreach ($skills as $skill) {
                $share = (float) ($skill->pivot->weight_percent ?? 100);
                $rows->push([
                    'subject_id' => $project->subject_id,
                    'id' => $project->id,
                    'title' => $project->title,
                    'type' => 'project',
                    'weight' => round((float) $project->weight_percent * ($share / 100), 2),
                    'score' => $score,
                    'model' => $project,
                ]);
            }
        }

        return $rows->groupBy('subject_id');
    }

    public function recordExamGrade(ExamAttempt $attempt, float $score): void
    {
        $attempt->update(['final_score' => $score, 'status' => 'corrected']);

        $exam = $attempt->exam;

        Grade::updateOrCreate(
            [
                'student_id' => $attempt->student_id,
                'type' => 'exam',
                'source_id' => $exam->id,
            ],
            [
                'subject_id' => $exam->subject_id,
                'skill_id' => $exam->skill_id,
                'value' => $score,
                'calculated_at' => now(),
            ],
        );

        app(GradeAggregator::class)->recalculateForStudent($attempt->student);
    }
}
