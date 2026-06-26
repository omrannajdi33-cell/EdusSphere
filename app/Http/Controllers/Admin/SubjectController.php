<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use App\Support\CatalogCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.subjects.index', [
            'adminNav' => 'subjects',
            'subjects' => Subject::withCount('skills')->ordered()->get(),
            'icons' => config('subjects.icons', []),
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.form', [
            'adminNav' => 'subjects',
            'subject' => new Subject(['color' => '#0891b2', 'icon' => 'book-open']),
            'icons' => config('subjects.icons', []),
        ]);
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());
        CatalogCache::flush();

        return redirect()
            ->route('admin.subjects.skills.index', $subject)
            ->with('success', 'Matière créée. Configure les compétences (total 100 %).');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.form', [
            'adminNav' => 'subjects',
            'subject' => $subject,
            'icons' => config('subjects.icons', []),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());
        CatalogCache::flush();

        return back()->with('success', 'Matière mise à jour.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();
        CatalogCache::flush();

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Matière supprimée.');
    }
}
