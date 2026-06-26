<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Answer;
use App\Models\Correction;
use App\Models\CorrectionHistory;
use App\Models\Grade;
use App\Models\Progression;
use App\Models\Student;
use App\Models\User;
use App\Services\Admin\DashboardStats;

class ActivityCorrectionService
{
    public function __construct(
        private ActivityScoreCalculator $calculator,
        private GradeAggregator $grades,
        private NotificationService $notifications,
    ) {}

    public function onSubmitted(Activity $activity, Student $student): Correction
    {
        $correction = Correction::updateOrCreate(
            [
                'student_id' => $student->id,
                'activity_id' => $activity->id,
            ],
            [
                'teacher_id' => User::query()
                    ->where('role', User::ROLE_TEACHER)
                    ->where('status', 'active')
                    ->value('id'),
                'status' => 'to_correct',
                'score' => null,
            ],
        );

        $this->log(
            $correction,
            $student->user_id ?? $correction->teacher_id,
            'submitted',
            'Copie soumise par l\'élève',
        );

        return $correction;
    }

    public function finalize(Activity $activity, Student $student, User $teacher, float $score, ?string $comment): Correction
    {
        $correction = Correction::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $correction->update([
            'teacher_id' => $teacher->id,
            'status' => 'validated',
            'score' => $score,
            'comment' => $comment,
        ]);

        Progression::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->update(['workflow_status' => 'corrected']);

        Grade::updateOrCreate(
            [
                'student_id' => $student->id,
                'type' => 'activity',
                'source_id' => $activity->id,
            ],
            [
                'subject_id' => $activity->subject_id,
                'skill_id' => $activity->skill_id,
                'value' => $score,
                'calculated_at' => now(),
            ],
        );

        $this->grades->recalculateForStudent($student);

        $this->log($correction, $teacher->id, 'validated', $comment ?: 'Correction validée');

        if ($student->user) {
            $this->notifications->notifyStudent($student->user, 'activity_corrected', [
                'activity_id' => $activity->id,
                'activity_title' => $activity->title,
                'score' => $score,
                'comment' => $comment,
                'url' => route('student.activities.play', $activity),
            ]);
        }

        DashboardStats::flush();

        return $correction->fresh();
    }

    public function returnToStudent(Activity $activity, Student $student, User $teacher, ?string $comment): Correction
    {
        $correction = Correction::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $correction->update([
            'teacher_id' => $teacher->id,
            'status' => 'returned',
            'comment' => $comment,
        ]);

        Progression::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->update(['workflow_status' => 'returned']);

        $this->log($correction, $teacher->id, 'returned', $comment ?: 'Renvoyée à l\'élève');

        if ($student->user) {
            $this->notifications->notifyStudent($student->user, 'activity_returned', [
                'activity_id' => $activity->id,
                'activity_title' => $activity->title,
                'comment' => $comment,
                'url' => route('student.activities.play', $activity),
            ]);
        }

        DashboardStats::flush();

        return $correction->fresh();
    }

    public function suggestedScore(Activity $activity, Student $student): ?float
    {
        return $this->calculator->calculate($activity, $student->id);
    }

    private function log(Correction $correction, int $userId, string $action, ?string $comment): void
    {
        CorrectionHistory::create([
            'correction_id' => $correction->id,
            'user_id' => $userId,
            'action' => $action,
            'comment' => $comment,
            'created_at' => now(),
        ]);
    }
}
