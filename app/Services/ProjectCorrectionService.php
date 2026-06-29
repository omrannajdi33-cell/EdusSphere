<?php

namespace App\Services;

use App\Models\Correction;
use App\Models\CorrectionHistory;
use App\Models\Grade;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\Student;
use App\Models\User;
use App\Services\Admin\DashboardStats;

class ProjectCorrectionService
{
    public function __construct(
        private GradeAggregator $grades,
        private NotificationService $notifications,
    ) {}

    public function onSubmitted(ProjectSubmission $submission): Correction
    {
        $project = $submission->project;
        $student = $submission->student;

        $correction = Correction::updateOrCreate(
            ['project_submission_id' => $submission->id],
            [
                'student_id' => $student->id,
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
            'Projet soumis par l\'élève',
        );

        $this->notifications->notifyTeachers('project_submitted', [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'url' => route('admin.projects.corrections.show', [$project, $student]),
        ]);

        DashboardStats::flush();

        return $correction;
    }

    public function finalize(ProjectSubmission $submission, User $teacher, float $score, ?string $comment): Correction
    {
        $project = $submission->project;
        $correction = Correction::query()
            ->where('project_submission_id', $submission->id)
            ->firstOrFail();

        $correction->update([
            'teacher_id' => $teacher->id,
            'status' => 'validated',
            'score' => $score,
            'comment' => $comment,
        ]);

        $submission->update(['workflow_status' => 'corrected']);

        Grade::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'type' => 'project',
                'source_id' => $project->id,
            ],
            [
                'subject_id' => $project->subject_id,
                'skill_id' => $project->skill_id,
                'value' => $score,
                'calculated_at' => now(),
            ],
        );

        $this->grades->recalculateForStudent($submission->student);

        $this->log($correction, $teacher->id, 'validated', $comment ?: 'Correction validée');

        if ($submission->student->user) {
            $this->notifications->notifyStudent($submission->student->user, 'project_corrected', [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'score' => $score,
                'comment' => $comment,
                'url' => route('student.projects.work', $project),
            ]);
        }

        DashboardStats::flush();

        return $correction->fresh();
    }

    public function returnToStudent(ProjectSubmission $submission, User $teacher, ?string $comment): Correction
    {
        $project = $submission->project;
        $correction = Correction::query()
            ->where('project_submission_id', $submission->id)
            ->firstOrFail();

        $correction->update([
            'teacher_id' => $teacher->id,
            'status' => 'returned',
            'comment' => $comment,
        ]);

        $submission->update(['workflow_status' => 'returned']);

        $this->log($correction, $teacher->id, 'returned', $comment ?: 'Renvoyé à l\'élève');

        if ($submission->student->user) {
            $this->notifications->notifyStudent($submission->student->user, 'project_returned', [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'comment' => $comment,
                'url' => route('student.projects.work', $project),
            ]);
        }

        DashboardStats::flush();

        return $correction->fresh();
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
