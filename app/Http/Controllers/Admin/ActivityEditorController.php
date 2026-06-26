<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreActivityPageRequest;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Models\Activity;
use App\Models\ActivityPage;
use App\Models\Answer;
use App\Models\Correction;
use App\Models\MediaFile;
use App\Models\Progression;
use App\Models\Question;
use App\Models\Student;
use App\Services\ActivityCorrectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityEditorController extends Controller
{
    public function storePage(StoreActivityPageRequest $request, Activity $activity): RedirectResponse
    {
        $order = ($activity->pages()->max('page_order') ?? 0) + 1;
        $data = $request->validated();

        $page = $activity->pages()->create([
            'page_order' => $order,
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $this->buildPageContent($data),
        ]);

        if ($data['type'] === 'pdf_worksheet' && $request->hasFile('pdf')) {
            $this->storePagePdf($request->file('pdf'), $activity, $page);
        }

        if ($request->hasFile('audio')) {
            $this->storePageAudio($request->file('audio'), $activity, $page);
        }

        return redirect()
            ->route('admin.activities.build', ['activity' => $activity, 'step' => 2])
            ->with('success', 'Étape ajoutée.');
    }

    public function destroyPage(Activity $activity, ActivityPage $page): RedirectResponse
    {
        abort_unless($page->activity_id === $activity->id, 404);

        if ($page->mediaFile && Storage::disk('local')->exists($page->mediaFile->path)) {
            Storage::disk('local')->delete($page->mediaFile->path);
        }

        $page->delete();

        return back()->with('success', 'Étape supprimée.');
    }

    public function storeQuestion(StoreQuestionRequest $request, Activity $activity, ActivityPage $page): RedirectResponse
    {
        abort_unless($page->activity_id === $activity->id, 404);
        abort_unless($page->type === 'interactive', 422);

        $order = ($page->questions()->max('display_order') ?? 0) + 1;
        $data = $request->validated();

        $page->questions()->create([
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'config' => $this->buildQuestionConfig($data),
            'display_order' => $order,
        ]);

        return back()->with('success', 'Question ajoutée.');
    }

    public function destroyQuestion(Activity $activity, Question $question): RedirectResponse
    {
        abort_unless($question->activityPage->activity_id === $activity->id, 404);
        $question->delete();

        return back()->with('success', 'Question supprimée.');
    }

    public function submissions(Activity $activity): View
    {
        $submissions = Progression::with(['student.user'])
            ->where('activity_id', $activity->id)
            ->whereIn('workflow_status', ['submitted', 'corrected', 'returned'])
            ->latest('submitted_at')
            ->get();

        $corrections = Correction::query()
            ->where('activity_id', $activity->id)
            ->whereIn('student_id', $submissions->pluck('student_id'))
            ->get()
            ->keyBy('student_id');

        return view('admin.activities.submissions', [
            'adminNav' => 'activities',
            'activity' => $activity,
            'submissions' => $submissions,
            'corrections' => $corrections,
        ]);
    }

    public function correct(Activity $activity, Student $student, ActivityCorrectionService $corrections): View
    {
        abort_unless(
            Progression::where('activity_id', $activity->id)
                ->where('student_id', $student->id)
                ->whereIn('workflow_status', ['submitted', 'corrected', 'returned'])
                ->exists(),
            404,
        );

        $activity->load(['pages.questions', 'pages.mediaFile', 'subject', 'skill']);

        $answers = Answer::where('student_id', $student->id)
            ->whereIn('activity_page_id', $activity->pages->pluck('id'))
            ->get()
            ->groupBy('activity_page_id');

        $correction = Correction::with(['history.user'])
            ->where('activity_id', $activity->id)
            ->where('student_id', $student->id)
            ->first();

        return view('admin.activities.correct', [
            'adminNav' => 'corrections',
            'activity' => $activity,
            'student' => $student,
            'answers' => $answers,
            'correction' => $correction,
            'suggestedScore' => $corrections->suggestedScore($activity, $student),
        ]);
    }

    public function saveCorrection(Request $request, Activity $activity, Student $student): JsonResponse
    {
        $data = $request->validate([
            'page_id' => ['required', 'exists:activity_pages,id'],
            'teacher_strokes' => ['nullable', 'array'],
        ]);

        abort_unless($activity->pages()->where('id', $data['page_id'])->exists(), 404);

        $answer = Answer::firstOrCreate(
            [
                'student_id' => $student->id,
                'question_id' => null,
                'activity_page_id' => $data['page_id'],
            ],
            ['content' => []],
        );

        $content = $answer->content ?? [];
        $content['teacher_strokes'] = $data['teacher_strokes'] ?? [];

        $answer->update(['content' => $content]);

        return response()->json(['saved' => true]);
    }

    public function finalizeCorrection(Request $request, Activity $activity, Student $student, ActivityCorrectionService $service): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->finalize($activity, $student, auth()->user(), (float) $data['score'], $data['comment'] ?? null);

        return redirect()
            ->route('admin.corrections.index')
            ->with('success', 'Correction validée et note enregistrée.');
    }

    public function returnCorrection(Request $request, Activity $activity, Student $student, ActivityCorrectionService $service): RedirectResponse
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $service->returnToStudent($activity, $student, auth()->user(), $data['comment']);

        return redirect()
            ->route('admin.activities.submissions', $activity)
            ->with('success', 'Copie renvoyée à l\'élève.');
    }

    protected function storePagePdf($file, Activity $activity, ActivityPage $page): void
    {
        $filename = Str::uuid().'.pdf';
        $path = $file->storeAs('activities/'.$activity->id.'/pages/'.$page->id, $filename, 'local');

        MediaFile::create([
            'activity_id' => $activity->id,
            'activity_page_id' => $page->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => $file->getSize(),
        ]);
    }

    protected function storePageAudio($file, Activity $activity, ActivityPage $page): void
    {
        $ext = $file->getClientOriginalExtension() ?: 'mp3';
        $filename = Str::uuid().'.'.$ext;
        $path = $file->storeAs('activities/'.$activity->id.'/pages/'.$page->id, $filename, 'local');

        MediaFile::create([
            'activity_id' => $activity->id,
            'activity_page_id' => $page->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?: 'audio/mpeg',
            'size_bytes' => $file->getSize(),
        ]);
    }

    /** @param array<string, mixed> $data */
    protected function buildPageContent(array $data): array
    {
        $content = ['body' => $data['body'] ?? ''];

        if (in_array($data['type'], ['reading_comprehension', 'recitation'], true)) {
            $content['passage'] = $data['passage'] ?? '';
            $content['allow_hide_text'] = true;
            $content['rtl'] = $data['type'] === 'recitation';
        }

        if ($data['type'] === 'rich_document') {
            $content['modes'] = ['text', 'draw'];
        }

        if ($data['type'] === 'math_scroll') {
            $content['scroll_height'] = 3200;
        }

        return $content;
    }

    protected function buildQuestionConfig(array $data): array
    {
        return match ($data['type']) {
            'mcq' => [
                'options' => array_values(array_filter($data['options'] ?? [], fn ($o) => filled($o['text'] ?? null))),
                'correct' => (int) ($data['correct_option'] ?? 0),
            ],
            'true_false' => [
                'correct' => ($data['correct_bool'] ?? 'true') === 'true',
            ],
            'multi_select' => [
                'options' => array_values(array_filter($data['options'] ?? [], fn ($o) => filled($o['text'] ?? null))),
                'correct' => array_map('intval', $data['correct_options'] ?? []),
            ],
            'short_text', 'long_text' => [
                'placeholder' => $data['placeholder'] ?? '',
            ],
            'numeric' => [
                'correct' => isset($data['correct_number']) ? (float) $data['correct_number'] : null,
                'tolerance' => (float) ($data['tolerance'] ?? 0),
            ],
            'fill_blank' => $this->buildFillBlankConfig($data),
            'ordering' => [
                'items' => array_values(array_filter($data['order_items'] ?? [], fn ($t) => filled($t))),
            ],
            'matching' => [
                'left' => array_values(array_filter($data['match_left'] ?? [], fn ($t) => filled($t))),
                'right' => array_values(array_filter($data['match_right'] ?? [], fn ($t) => filled($t))),
            ],
            'choice_cards' => [
                'cards' => array_values(array_filter($data['cards'] ?? [], fn ($c) => filled($c['text'] ?? null))),
                'correct' => (int) ($data['correct_card'] ?? 0),
            ],
            default => [],
        };
    }

    protected function buildFillBlankConfig(array $data): array
    {
        $sentence = $data['blank_sentence'] ?? '';
        $parts = preg_split('/_{3,}/', $sentence) ?: [$sentence];
        $answers = array_values(array_filter($data['blank_answers'] ?? [], fn ($a) => filled($a)));

        return [
            'parts' => $parts,
            'answers' => $answers,
            'display' => $sentence,
        ];
    }
}
