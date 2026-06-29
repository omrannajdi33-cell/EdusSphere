<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\Report;
use App\Models\ReportPeriod;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

class BulletinGeneratorService
{
    public function __construct(
        private BulletinPdfService $pdf,
    ) {}

    /** @return Collection<int, ReportPeriod> */
    public function periodsIncluded(ReportPeriod $period): Collection
    {
        return ReportPeriod::query()
            ->where('school_year', $period->school_year)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (ReportPeriod $p) => $p->sort_order <= $period->sort_order)
            ->values();
    }

    /** @return array<string, mixed> */
    public function buildPayload(Student $student, ReportPeriod $period): array
    {
        $student->loadMissing(['schoolLevel', 'classGroup']);
        $includedPeriods = $this->periodsIncluded($period);
        $periodIds = $includedPeriods->pluck('id');

        $exams = Exam::query()
            ->with(['skill', 'subject'])
            ->whereIn('report_period_id', $periodIds)
            ->where('status', '!=', 'draft')
            ->get();

        $projects = Project::query()
            ->with(['skills', 'subject'])
            ->whereIn('report_period_id', $periodIds)
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

        $evaluationsBySubject = $this->collectEvaluationsBySubject($exams, $projects, $attempts, $projectScores);

        $subjects = [];
        $subjectAverages = [];
        $subjectGradeSum = 0.0;
        $subjectGradeCount = 0;

        foreach (Subject::with('skills')->ordered()->get() as $subject) {
            $subjectEvaluations = $evaluationsBySubject->get($subject->id, collect());
            if ($subjectEvaluations->isEmpty()) {
                continue;
            }

            $skillsPayload = [];
            $skillsWithGrades = [];

            $skillIds = $subjectEvaluations->pluck('skill_id')->filter()->unique();
            $skills = Skill::query()
                ->whereIn('id', $skillIds)
                ->orderBy('display_order')
                ->get();

            if ($skills->isEmpty()) {
                $skills = collect([(object) ['id' => null, 'name' => 'Général']]);
            }

            foreach ($skills as $skill) {
                $skillEvaluations = $skill->id
                    ? $subjectEvaluations->where('skill_id', $skill->id)
                    : $subjectEvaluations;

                if ($skillEvaluations->isEmpty()) {
                    continue;
                }

                $periodBlocks = [];
                $periodGrades = [];

                foreach ($includedPeriods as $includedPeriod) {
                    $periodEvaluations = $skillEvaluations->where('report_period_id', $includedPeriod->id);
                    $rows = [];
                    $earned = 0.0;
                    $weightDone = 0.0;

                    foreach ($periodEvaluations as $evaluation) {
                        $score = $evaluation['score'];
                        $weight = (float) $evaluation['weight'];

                        $rows[] = [
                            'id' => $evaluation['id'],
                            'title' => $evaluation['title'],
                            'type' => $evaluation['type'],
                            'weight' => $weight,
                            'score' => $score,
                            'done' => $score !== null,
                        ];

                        if ($score !== null && $weight > 0) {
                            $earned += $score * ($weight / 100);
                            $weightDone += $weight;
                        }
                    }

                    $periodAverage = $weightDone > 0 ? round($earned / ($weightDone / 100), 2) : null;

                    if ($periodAverage !== null) {
                        $periodGrades[] = $periodAverage;
                    }

                    $periodBlocks[] = [
                        'period_id' => $includedPeriod->id,
                        'label' => $includedPeriod->label,
                        'sort_order' => $includedPeriod->sort_order,
                        'evaluations' => $rows,
                        'exams' => $rows,
                        'average' => $periodAverage,
                        'weight_done' => round($weightDone, 2),
                    ];
                }

                $skillAverage = count($periodGrades) > 0
                    ? round(array_sum($periodGrades) / count($periodGrades), 2)
                    : null;

                if ($skillAverage !== null) {
                    $skillsWithGrades[] = $skillAverage;
                }

                $skillsPayload[] = [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'periods' => $periodBlocks,
                    'average' => $skillAverage,
                ];
            }

            $subjectAverage = count($skillsWithGrades) > 0
                ? round(array_sum($skillsWithGrades) / count($skillsWithGrades), 2)
                : null;

            if ($subjectAverage !== null) {
                $subjectAverages[$subject->id] = $subjectAverage;
                $subjectGradeSum += $subjectAverage;
                $subjectGradeCount++;
            }

            $subjects[] = [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon,
                'color' => $subject->color,
                'average' => $subjectAverage,
                'skills' => $skillsPayload,
            ];
        }

        $generalAverage = $subjectGradeCount > 0
            ? round($subjectGradeSum / $subjectGradeCount, 2)
            : null;

        return [
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'birth_date' => $student->birth_date?->format('d/m/Y'),
                'school_level' => $student->schoolLevel?->name,
                'class_group' => $student->classGroup?->name,
            ],
            'school_year' => $period->school_year,
            'period' => [
                'id' => $period->id,
                'label' => $period->label,
                'sort_order' => $period->sort_order,
            ],
            'included_periods' => $includedPeriods->map(fn (ReportPeriod $p) => [
                'id' => $p->id,
                'label' => $p->label,
                'sort_order' => $p->sort_order,
            ])->values()->all(),
            'general_average' => $generalAverage,
            'subjects' => $subjects,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, Collection<int, ExamAttempt>>  $attempts
     * @param  Collection<int, float|null>  $projectScores
     * @return Collection<int, Collection<int, array<string, mixed>>>
     */
    private function collectEvaluationsBySubject(
        Collection $exams,
        Collection $projects,
        Collection $attempts,
        Collection $projectScores,
    ): Collection {
        $rows = collect();

        foreach ($exams as $exam) {
            $best = $attempts->get($exam->id)?->sortByDesc('final_score')->first();
            $score = $best?->final_score !== null ? (float) $best->final_score : null;

            $rows->push([
                'subject_id' => $exam->subject_id,
                'skill_id' => $exam->skill_id,
                'report_period_id' => $exam->report_period_id,
                'id' => $exam->id,
                'title' => $exam->title,
                'type' => 'exam',
                'weight' => (float) $exam->weight_percent,
                'score' => $score,
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
                $effectiveWeight = round((float) $project->weight_percent * ($share / 100), 2);

                $rows->push([
                    'subject_id' => $project->subject_id,
                    'skill_id' => $skill->id,
                    'report_period_id' => $project->report_period_id,
                    'id' => $project->id,
                    'title' => $project->title,
                    'type' => 'project',
                    'weight' => $effectiveWeight,
                    'score' => $score,
                ]);
            }
        }

        return $rows->groupBy('subject_id');
    }

    public function generate(Student $student, ReportPeriod $period, User $teacher, ?string $comment = null): Report
    {
        $payload = $this->buildPayload($student, $period);

        $subjectSummary = collect($payload['subjects'])
            ->filter(fn (array $s) => $s['average'] !== null)
            ->mapWithKeys(fn (array $s) => [$s['id'] => $s['average']])
            ->all();

        $report = Report::updateOrCreate(
            [
                'student_id' => $student->id,
                'report_period_id' => $period->id,
            ],
            [
                'period_label' => $period->label,
                'general_average' => $payload['general_average'] ?? 0,
                'subject_averages' => $subjectSummary,
                'payload' => $payload,
                'comments' => $comment,
                'generated_by' => $teacher->id,
                'generated_at' => now(),
            ],
        );

        $pdfPath = $this->pdf->store($report, $payload);
        $report->update(['pdf_path' => $pdfPath]);

        return $report->fresh(['student', 'reportPeriod', 'generatedBy']);
    }

    /** @return list<Report> */
    public function generateForClass(ReportPeriod $period, User $teacher, ?int $classGroupId = null, ?string $comment = null): array
    {
        $query = Student::query()->with(['schoolLevel', 'classGroup']);

        if ($classGroupId) {
            $query->where('class_group_id', $classGroupId);
        }

        $reports = [];

        foreach ($query->get() as $student) {
            $reports[] = $this->generate($student, $period, $teacher, $comment);
        }

        return $reports;
    }
}
