<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotionCategoryRequest;
use App\Http\Requests\Admin\StoreNotionRequest;
use App\Http\Requests\Admin\UpdateNotionCategoryRequest;
use App\Http\Requests\Admin\UpdateNotionRequest;
use App\Models\Notion;
use App\Models\NotionCategory;
use App\Models\SchoolLevel;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotionController extends Controller
{
    public function index(Request $request): View
    {
        $calendarLevels = SchoolLevel::query()
            ->whereIn('name', config('schedule.calendar_levels', []))
            ->orderBy('display_order')
            ->get();

        abort_if($calendarLevels->isEmpty(), 404, 'Aucun niveau configuré pour les notions.');

        $levelId = $request->integer('level');
        $activeLevel = $calendarLevels->firstWhere('id', $levelId) ?? $calendarLevels->first();

        $subjects = Subject::with(['skills'])->ordered()->get();
        $subjectId = $request->integer('subject') ?: $subjects->first()?->id;
        $subject = $subjects->firstWhere('id', $subjectId) ?? $subjects->first();

        return view('admin.notions.index', [
            'adminNav' => 'notions',
            'calendarLevels' => $calendarLevels,
            'activeLevel' => $activeLevel,
            'subjects' => $subjects,
            'subject' => $subject,
            'categories' => $subject
                ? $subject->notionCategories()
                    ->with('notions')
                    ->where('school_level_id', $activeLevel->id)
                    ->orderBy('display_order')
                    ->get()
                : collect(),
        ]);
    }

    public function storeCategory(StoreNotionCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $order = (NotionCategory::where('subject_id', $data['subject_id'])
            ->where('school_level_id', $data['school_level_id'])
            ->max('display_order') ?? 0) + 1;

        NotionCategory::create([
            ...$data,
            'display_order' => $order,
        ]);

        return $this->redirectToSubject($data['subject_id'], $data['school_level_id'], 'Catégorie ajoutée.');
    }

    public function updateCategory(UpdateNotionCategoryRequest $request, NotionCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return $this->redirectToSubject($category->subject_id, $category->school_level_id, 'Catégorie mise à jour.');
    }

    public function destroyCategory(NotionCategory $category): RedirectResponse
    {
        $subjectId = $category->subject_id;
        $levelId = $category->school_level_id;
        $category->delete();

        return $this->redirectToSubject($subjectId, $levelId, 'Catégorie supprimée.');
    }

    public function storeNotion(StoreNotionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $category = NotionCategory::findOrFail($data['notion_category_id']);
        $order = ($category->notions()->max('display_order') ?? 0) + 1;

        Notion::create([
            ...$data,
            'subject_id' => $category->subject_id,
            'display_order' => $order,
        ]);

        return $this->redirectToSubject($category->subject_id, $category->school_level_id, 'Notion ajoutée.');
    }

    public function updateNotion(UpdateNotionRequest $request, Notion $notion): RedirectResponse
    {
        $notion->update($request->validated());
        $notion->load('category');

        return $this->redirectToSubject($notion->subject_id, $notion->category?->school_level_id, 'Notion mise à jour.');
    }

    public function destroyNotion(Notion $notion): RedirectResponse
    {
        $notion->load('category');
        $subjectId = $notion->subject_id;
        $levelId = $notion->category?->school_level_id;
        $notion->delete();

        return $this->redirectToSubject($subjectId, $levelId, 'Notion supprimée.');
    }

    private function redirectToSubject(int $subjectId, ?int $levelId, string $message): RedirectResponse
    {
        $params = ['subject' => $subjectId];

        if ($levelId) {
            $params['level'] = $levelId;
        }

        return redirect()
            ->route('admin.notions.index', $params)
            ->with('success', $message);
    }
}
