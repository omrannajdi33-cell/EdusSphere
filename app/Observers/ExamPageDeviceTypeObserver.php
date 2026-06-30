<?php

namespace App\Observers;

use App\Models\Exam;
use App\Models\ExamPage;
use App\Services\DeviceTypeResolver;

class ExamPageDeviceTypeObserver
{
    public function __construct(
        private DeviceTypeResolver $resolver,
    ) {}

    public function saved(ExamPage $page): void
    {
        $this->refresh($page->exam_id);
    }

    public function deleted(ExamPage $page): void
    {
        $this->refresh($page->exam_id);
    }

    private function refresh(int $examId): void
    {
        $exam = Exam::query()->with('pages')->find($examId);

        if (! $exam) {
            return;
        }

        $exam->updateQuietly([
            'device_type' => $this->resolver->forExam($exam),
        ]);
    }
}
