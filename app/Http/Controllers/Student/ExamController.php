<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ActivityScoreCalculator;
use App\Services\BulletinService;
use App\Services\ExamCorrectionService;
use App\Services\ExamScoreCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        if (! $student) {
            return view('student.exams.index', [
                'activeNav' => 'exams',
                'upcoming' => collect(),
                'active' => collect(),
                'finished' => collect(),
                'attempts' => collect(),
                'student' => null,
            ]);
        }

        $exams = Exam::with(['subject', 'skill'])
            ->where('status', '!=', 'draft')
            ->orderBy('opens_at')
            ->get();

        $attempts = ExamAttempt::where('student_id', $student->id)
            ->get()
            ->groupBy('exam_id');

        $upcoming = $exams->filter(fn (Exam $e) => $e->isUpcoming());
        $active = $exams->filter(function (Exam $e) use ($student, $attempts) {
            if (! $e->isOpenNow()) {
                return false;
            }

            $examAttempts = $attempts->get($e->id) ?? collect();

            if ($examAttempts->contains(fn ($a) => $a->status === 'in_progress')) {
                return true;
            }

            return $e->canStudentStart($student->id);
        });
        $finished = $exams->filter(fn (Exam $e) => $e->isFinished() || $attempts->get($e->id)?->contains(fn ($a) => in_array($a->status, ['submitted', 'corrected'], true)));

        return view('student.exams.index', [
            'activeNav' => 'exams',
            'upcoming' => $upcoming,
            'active' => $active,
            'finished' => $finished,
            'attempts' => $attempts,
            'student' => $student,
        ]);
    }

    public function start(Exam $exam): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        if (! $exam->canStudentStart($student->id)) {
            $hasFinished = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'corrected'])
                ->exists();

            if ($hasFinished) {
                return redirect()
                    ->route('student.exams.index')
                    ->with('info', 'Tu as déjà soumis cet examen. Consulte la section « Terminés » ou ton bulletin.');
            }

            abort(403);
        }

        $attempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (! $attempt) {
            $used = $exam->studentAttemptCount($student->id);
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => now(),
                'attempts_remaining' => max(0, $exam->max_attempts - $used - 1),
                'status' => 'in_progress',
            ]);
        }

        return redirect()->route('student.exams.take', $attempt);
    }

    public function take(ExamAttempt $attempt): View|RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $attempt->student_id === $student->id, 403);

        if ($attempt->status !== 'in_progress') {
            $message = match ($attempt->status) {
                'corrected' => 'Cet examen a déjà été corrigé. Consulte ton bulletin pour voir ta note.',
                'submitted' => 'Cet examen a déjà été soumis. Tu recevras une notification quand il sera corrigé.',
                default => 'Cet examen n\'est plus accessible.',
            };

            return redirect()
                ->route('student.dashboard')
                ->with('info', $message);
        }

        $exam = $attempt->exam()->with([
            'pages.questions',
            'sourceActivity.pages.questions',
            'sourceActivity.subject',
            'subject',
            'skill',
        ])->firstOrFail();

        abort_unless($exam->contentReady() && $exam->isOpenNow(), 404);

        $usesOwnContent = $exam->hasOwnContent();
        $content = $usesOwnContent ? $exam : $exam->sourceActivity;
        $endsAt = $attempt->started_at->copy()->addMinutes($exam->duration_minutes);

        if (now()->gte($endsAt)) {
            $this->finalizeAttempt($attempt);

            return redirect()
                ->route('student.exams.index')
                ->with('success', 'Temps écoulé — examen soumis automatiquement.');
        }

        $answers = Answer::where('student_id', $student->id)
            ->where('exam_attempt_id', $attempt->id)
            ->get()
            ->groupBy(fn ($a) => $usesOwnContent ? $a->exam_page_id : $a->activity_page_id);

        return view('student.exams.take', [
            'activeNav' => 'exams',
            'exam' => $exam,
            'attempt' => $attempt,
            'content' => $content,
            'usesOwnContent' => $usesOwnContent,
            'answers' => $answers,
            'endsAt' => $endsAt,
        ]);
    }

    public function save(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $attempt->student_id === $student->id && $attempt->status === 'in_progress', 403);

        $exam = $attempt->exam()->with('pages')->firstOrFail();
        abort_unless($exam->isOpenNow(), 404);

        $usesOwnContent = $exam->hasOwnContent();

        $data = $request->validate([
            'page_id' => ['required', 'integer'],
            'page_order' => ['required', 'integer', 'min:1'],
            'total_pages' => ['required', 'integer', 'min:1'],
            'responses' => ['nullable', 'array'],
            'canvas' => ['nullable', 'array'],
        ]);

        $pageId = (int) $data['page_id'];

        if ($usesOwnContent) {
            abort_unless($exam->pages()->where('id', $pageId)->exists(), 404);
        } else {
            abort_unless($exam->sourceActivity?->pages()->where('id', $pageId)->exists(), 404);
        }

        foreach ($data['responses'] ?? [] as $questionId => $value) {
            $attrs = [
                'student_id' => $student->id,
                'exam_attempt_id' => $attempt->id,
            ];

            if ($usesOwnContent) {
                $attrs['exam_question_id'] = (int) $questionId;
                $attrs['exam_page_id'] = $pageId;
                $attrs['question_id'] = null;
                $attrs['activity_page_id'] = null;
            } else {
                $attrs['question_id'] = (int) $questionId;
                $attrs['activity_page_id'] = $pageId;
            }

            Answer::updateOrCreate($attrs, ['content' => ['value' => $value]]);
        }

        if (isset($data['canvas'])) {
            $canvasAttrs = [
                'student_id' => $student->id,
                'exam_attempt_id' => $attempt->id,
                'question_id' => null,
                'exam_question_id' => null,
            ];

            if ($usesOwnContent) {
                $canvasAttrs['exam_page_id'] = $pageId;
                $canvasAttrs['activity_page_id'] = null;
            } else {
                $canvasAttrs['activity_page_id'] = $pageId;
            }

            Answer::updateOrCreate($canvasAttrs, ['content' => ['canvas' => $data['canvas']]]);
        }

        $answerCount = Answer::where('exam_attempt_id', $attempt->id)
            ->when($usesOwnContent, fn ($q) => $q->whereNotNull('exam_question_id'))
            ->when(! $usesOwnContent, fn ($q) => $q->whereNotNull('question_id'))
            ->count();

        $attempt->update([
            'pages_visited' => max($attempt->pages_visited, (int) $data['page_order']),
            'answers_count' => $answerCount,
        ]);

        return response()->json(['saved' => true]);
    }

    public function submit(ExamAttempt $attempt): JsonResponse|RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $attempt->student_id === $student->id && $attempt->status === 'in_progress', 403);

        $this->finalizeAttempt($attempt);

        if (request()->expectsJson()) {
            return response()->json(['submitted' => true]);
        }

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Examen soumis ! Bravo.');
    }

    private function finalizeAttempt(ExamAttempt $attempt): void
    {
        $exam = $attempt->exam;
        $examCorrection = app(ExamCorrectionService::class);

        $attempt->update([
            'status' => 'submitted',
            'finished_at' => now(),
            'duration_seconds' => $attempt->started_at->diffInSeconds(now()),
        ]);

        if ($examCorrection->needsManualCorrection($exam)) {
            $examCorrection->onSubmitted($attempt);

            $autoScore = $exam->hasOwnContent()
                ? app(ExamScoreCalculator::class)->calculate($exam, $attempt->id)
                : null;

            if ($autoScore !== null) {
                $attempt->update(['final_score' => $autoScore]);
            }

            return;
        }

        $score = $exam->hasOwnContent()
            ? app(ExamScoreCalculator::class)->calculate($exam, $attempt->id)
            : ($exam->sourceActivity
                ? app(ActivityScoreCalculator::class)->calculateForAttempt($exam->sourceActivity, $attempt->id)
                : null);

        if ($score !== null) {
            app(BulletinService::class)->recordExamGrade($attempt, $score);
        }
    }
}
