<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ExamAttempt;
use App\Services\ExamCorrectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    public function correct(ExamAttempt $attempt): View
    {
        $attempt->load(['exam.pages.questions', 'student.user']);

        $answers = Answer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->get()
            ->keyBy('exam_question_id');

        return view('admin.exams.correct', [
            'adminNav' => 'corrections',
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'answers' => $answers,
            'suggestedScore' => app(\App\Services\ExamScoreCalculator::class)->calculate($attempt->exam, $attempt->id),
        ]);
    }

    public function finalize(Request $request, ExamAttempt $attempt, ExamCorrectionService $service): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->finalize($attempt, auth()->user(), (float) $data['score'], $data['comment'] ?? null);

        return redirect()
            ->route('admin.corrections.index')
            ->with('success', 'Examen corrigé et note enregistrée.');
    }
}
