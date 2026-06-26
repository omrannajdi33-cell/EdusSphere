<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Answer;
use App\Models\Question;
use App\Support\QuestionGrader;

class ActivityScoreCalculator
{
    public function calculate(Activity $activity, int $studentId): ?float
    {
        return $this->calculateFromAnswers($activity, Answer::query()
            ->where('student_id', $studentId)
            ->whereNull('exam_attempt_id')
            ->whereIn('question_id', $this->gradableQuestionIds($activity))
            ->get()
            ->keyBy('question_id'));
    }

    public function calculateForAttempt(Activity $activity, int $attemptId): ?float
    {
        $activity->loadMissing('pages.questions');

        $questionIds = $this->gradableQuestionIds($activity);
        if ($questionIds->isEmpty()) {
            return null;
        }

        $answers = Answer::query()
            ->where('exam_attempt_id', $attemptId)
            ->whereIn('question_id', $questionIds)
            ->get()
            ->keyBy('question_id');

        return $this->calculateFromAnswers($activity, $answers);
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function gradableQuestionIds(Activity $activity): \Illuminate\Support\Collection
    {
        $activity->loadMissing('pages.questions');

        return $activity->pages
            ->flatMap(fn ($page) => $page->questions)
            ->filter(fn (Question $q) => QuestionGrader::isAutoGradable($q->type))
            ->pluck('id');
    }

    /** @param \Illuminate\Support\Collection<int, Answer> $answers */
    private function calculateFromAnswers(Activity $activity, \Illuminate\Support\Collection $answers): ?float
    {
        $activity->loadMissing('pages.questions');

        $questions = $activity->pages
            ->flatMap(fn ($page) => $page->questions)
            ->filter(fn (Question $q) => QuestionGrader::isAutoGradable($q->type))
            ->values();

        if ($questions->isEmpty()) {
            return null;
        }

        $correct = 0;

        foreach ($questions as $question) {
            $answer = $answers->get($question->id);
            if ($answer && QuestionGrader::isCorrect($question, $answer->content['value'] ?? null)) {
                $correct++;
            }
        }

        return round($correct / $questions->count() * 100, 2);
    }
}
