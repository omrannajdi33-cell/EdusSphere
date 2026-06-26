<?php

namespace App\Services;

use App\Models\Correction;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\Admin\DashboardStats;

class ExamCorrectionService
{
    /** @var list<string> */
    private const AUTO_GRADABLE = ['mcq', 'true_false', 'multi_select', 'numeric', 'choice_cards'];

    public function __construct(
        private NotificationService $notifications,
        private BulletinService $bulletin,
    ) {}

    public function needsManualCorrection(Exam $exam): bool
    {
        $exam->loadMissing('pages.questions');

        return $exam->pages
            ->flatMap(fn ($page) => $page->questions)
            ->contains(fn (ExamQuestion $q) => ! in_array($q->type, self::AUTO_GRADABLE, true));
    }

    public function onSubmitted(ExamAttempt $attempt): Correction
    {
        $attempt->loadMissing('exam', 'student.user');

        $correction = Correction::updateOrCreate(
            [
                'student_id' => $attempt->student_id,
                'exam_attempt_id' => $attempt->id,
            ],
            [
                'activity_id' => null,
                'teacher_id' => User::query()
                    ->where('role', User::ROLE_TEACHER)
                    ->where('status', 'active')
                    ->value('id'),
                'status' => 'to_correct',
                'score' => null,
            ],
        );

        $this->notifications->notifyTeachers('exam_submitted', [
            'exam_id' => $attempt->exam_id,
            'exam_title' => $attempt->exam->title,
            'student_name' => $attempt->student->full_name,
            'url' => route('admin.exams.attempts.correct', $attempt),
        ]);

        DashboardStats::flush();

        return $correction;
    }

    public function finalize(ExamAttempt $attempt, User $teacher, float $score, ?string $comment): Correction
    {
        $attempt->loadMissing('exam', 'student.user');

        $correction = Correction::query()
            ->where('exam_attempt_id', $attempt->id)
            ->firstOrFail();

        $correction->update([
            'teacher_id' => $teacher->id,
            'status' => 'validated',
            'score' => $score,
            'comment' => $comment,
        ]);

        $attempt->update([
            'status' => 'corrected',
            'final_score' => $score,
        ]);

        $this->bulletin->recordExamGrade($attempt, $score);

        if ($attempt->student->user) {
            $this->notifications->notifyStudent($attempt->student->user, 'exam_corrected', [
                'exam_id' => $attempt->exam_id,
                'exam_title' => $attempt->exam->title,
                'score' => $score,
                'comment' => $comment,
                'url' => route('student.bulletin.index'),
            ]);
        }

        DashboardStats::flush();

        return $correction->fresh();
    }
}
