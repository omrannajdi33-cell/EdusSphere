<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Student;

class GradeAggregator
{
    public function recalculateForStudent(Student $student): void
    {
        Grade::query()
            ->where('student_id', $student->id)
            ->whereIn('type', ['general', 'average_subject', 'average_skill'])
            ->delete();

        $activityGrades = Grade::query()
            ->where('student_id', $student->id)
            ->where('type', 'activity')
            ->get();

        if ($activityGrades->isEmpty()) {
            return;
        }

        Grade::create([
            'student_id' => $student->id,
            'subject_id' => null,
            'skill_id' => null,
            'value' => round($activityGrades->avg('value'), 2),
            'type' => 'general',
            'source_id' => null,
            'calculated_at' => now(),
        ]);

        foreach ($activityGrades->groupBy('subject_id') as $subjectId => $grades) {
            if (! $subjectId) {
                continue;
            }

            Grade::create([
                'student_id' => $student->id,
                'subject_id' => $subjectId,
                'skill_id' => null,
                'value' => round($grades->avg('value'), 2),
                'type' => 'average_subject',
                'source_id' => $subjectId,
                'calculated_at' => now(),
            ]);
        }

        foreach ($activityGrades->groupBy('skill_id') as $skillId => $grades) {
            if (! $skillId) {
                continue;
            }

            Grade::create([
                'student_id' => $student->id,
                'subject_id' => $grades->first()->subject_id,
                'skill_id' => $skillId,
                'value' => round($grades->avg('value'), 2),
                'type' => 'average_skill',
                'source_id' => $skillId,
                'calculated_at' => now(),
            ]);
        }
    }

    /** @return array{general: float|null, by_subject: array<int, float>, activity_grades: \Illuminate\Support\Collection<int, Grade>} */
    public function forStudent(Student $student): array
    {
        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->get();

        $general = $grades->firstWhere('type', 'general');

        return [
            'general' => $general ? (float) $general->value : null,
            'by_subject' => $grades
                ->where('type', 'average_subject')
                ->mapWithKeys(fn (Grade $g) => [$g->subject_id => (float) $g->value])
                ->all(),
            'activity_grades' => $grades->where('type', 'activity')->sortByDesc('calculated_at'),
        ];
    }
}
