<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(): View
    {
        return view('admin.lessons.index', [
            'adminNav' => 'lessons',
            'lessons' => Lesson::with(['subject', 'skill', 'schoolLevel'])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Lesson(['status' => 'draft']));
    }

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        $lesson = Lesson::create([
            ...$request->validated(),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->with('success', 'Leçon créée. Tu peux la publier quand elle est prête.');
    }

    public function edit(Lesson $lesson): View
    {
        $lesson->load(['subject', 'skill', 'schoolLevel', 'mediaFiles']);

        return $this->formView($lesson);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());

        return back()->with('success', 'Leçon mise à jour.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', 'Leçon supprimée.');
    }

    public function publish(Lesson $lesson): RedirectResponse
    {
        $lesson->update([
            'status' => 'published',
            'published_at' => $lesson->published_at ?? now(),
        ]);

        return back()->with('success', 'Leçon publiée ! Les élèves peuvent la voir.');
    }

    public function unpublish(Lesson $lesson): RedirectResponse
    {
        $lesson->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return back()->with('success', 'Leçon dépubliée.');
    }

    protected function formView(Lesson $lesson): View
    {
        if ($lesson->exists) {
            $lesson->loadMissing('mediaFiles');
        }

        return view('admin.lessons.form', [
            'adminNav' => 'lessons',
            'lesson' => $lesson,
            'subjects' => Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get()),
            'skills' => Cache::remember('catalog.skills', 3600, fn () => Skill::orderBy('name')->get()),
            'levels' => SchoolLevel::orderBy('display_order')->get(),
        ]);
    }
}
