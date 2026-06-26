<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Support\QuestionGrader;

class ExamScoreCalculator
{
    public function calculate(Exam $exam, int $attemptId): ?float
    {
        $exam->loadMissing('pages.questions');

        $questions = $exam->pages
            ->flatMap(fn ($page) => $page->questions)
            ->filter(fn (ExamQuestion $q) => QuestionGrader::isAutoGradable($q->type))
            ->values();

        if ($questions->isEmpty()) {
            return null;
        }

        $answers = Answer::query()
            ->where('exam_attempt_id', $attemptId)
            ->whereIn('exam_question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('exam_question_id');

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
