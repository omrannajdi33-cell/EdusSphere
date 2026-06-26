<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\Grade;
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
        $exams = Exam::query()
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft')
            ->get()
            ->groupBy('subject_id');

        $attempts = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('exam_id', $exams->flatten()->pluck('id'))
            ->whereIn('status', ['submitted', 'corrected'])
            ->whereNotNull('final_score')
            ->get()
            ->groupBy('exam_id');

        $result = [];

        foreach ($subjects as $subject) {
            $subjectExams = $exams->get($subject->id, collect());
            $result[$subject->id] = $this->buildSubjectCard($subject, $subjectExams, $attempts);
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function subjectWeightsForPeriod(ReportPeriod $period, ?int $subjectId = null): array
    {
        $query = Exam::query()
            ->where('report_period_id', $period->id)
            ->where('status', '!=', 'draft');

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $exams = $query->with('subject')->get()->groupBy('subject_id');
        $cards = [];

        foreach (Subject::ordered()->get() as $subject) {
            if ($subjectId && $subject->id !== $subjectId) {
                continue;
            }
            $subjectExams = $exams->get($subject->id, collect());
            $totalWeight = round($subjectExams->sum('weight_percent'), 2);
            $cards[$subject->id] = [
                'subject' => $subject,
                'exams' => $subjectExams,
                'total_weight' => $totalWeight,
                'missing_weight' => max(0, round(100 - $totalWeight, 2)),
                'is_complete' => abs(100 - $totalWeight) < 0.01,
            ];
        }

        return $cards;
    }

    /** @param Collection<int, Exam> $exams @param Collection<int, Collection<int, ExamAttempt>> $attempts */
    private function buildSubjectCard(Subject $subject, Collection $exams, Collection $attempts): array
    {
        $totalWeight = round($exams->sum('weight_percent'), 2);
        $completedWeight = 0.0;
        $earnedPoints = 0.0;
        $examDetails = [];

        foreach ($exams as $exam) {
            $best = $attempts->get($exam->id)?->sortByDesc('final_score')->first();
            $score = $best?->final_score !== null ? (float) $best->final_score : null;
            $weight = (float) $exam->weight_percent;

            if ($score !== null && $weight > 0) {
                $completedWeight += $weight;
                $earnedPoints += $score * ($weight / 100);
            }

            $examDetails[] = [
                'exam' => $exam,
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
            'exams' => $examDetails,
            'is_bulletin_complete' => $totalWeight >= 99.99 && $completedWeight >= 99.99,
        ];
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
