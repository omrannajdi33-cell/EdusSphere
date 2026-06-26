<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreActivityPageRequest;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Models\Exam;
use App\Models\ExamPage;
use App\Models\ExamQuestion;
use Illuminate\Http\RedirectResponse;

class ExamEditorController extends Controller
{
    use BuildsQuestionConfig;

    public function storePage(StoreActivityPageRequest $request, Exam $exam): RedirectResponse
    {
        $order = ($exam->pages()->max('page_order') ?? 0) + 1;
        $data = $request->validated();

        $exam->pages()->create([
            'page_order' => $order,
            'title' => $data['title'],
            'type' => $data['type'] === 'pdf_worksheet' ? 'interactive' : $data['type'],
            'content' => ['body' => $data['body'] ?? ''],
        ]);

        return redirect()
            ->route('admin.exams.build', $exam)
            ->with('success', 'Étape ajoutée.');
    }

    public function destroyPage(Exam $exam, ExamPage $page): RedirectResponse
    {
        abort_unless($page->exam_id === $exam->id, 404);
        $page->delete();

        return back()->with('success', 'Étape supprimée.');
    }

    public function storeQuestion(StoreQuestionRequest $request, Exam $exam, ExamPage $page): RedirectResponse
    {
        abort_unless($page->exam_id === $exam->id, 404);
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

    public function destroyQuestion(Exam $exam, ExamQuestion $question): RedirectResponse
    {
        abort_unless($question->examPage->exam_id === $exam->id, 404);
        $question->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
