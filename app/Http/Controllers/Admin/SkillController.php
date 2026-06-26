<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use App\Models\Subject;
use App\Support\CatalogCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(Subject $subject): View
    {
        $skills = $subject->skills()->orderBy('display_order')->get();
        $total = Skill::subjectTotalWeight($subject->id);

        return view('admin.skills.index', [
            'adminNav' => 'subjects',
            'subject' => $subject,
            'skills' => $skills,
            'total' => $total,
            'isValidTotal' => Skill::isValidSubjectTotal($subject->id),
        ]);
    }

    public function create(Subject $subject): View
    {
        return view('admin.skills.form', [
            'adminNav' => 'subjects',
            'subject' => $subject,
            'skill' => new Skill,
            'total' => Skill::subjectTotalWeight($subject->id),
        ]);
    }

    public function store(StoreSkillRequest $request, Subject $subject): RedirectResponse
    {
        $data = $request->validated();
        $order = ($subject->skills()->max('display_order') ?? 0) + 1;

        $subject->skills()->create([
            'name' => $data['name'],
            'weight_percent' => $data['weight_percent'],
            'display_order' => $order,
        ]);

        CatalogCache::flush();

        return redirect()
            ->route('admin.subjects.skills.index', $subject)
            ->with('success', 'Compétence ajoutée.');
    }

    public function edit(Subject $subject, Skill $skill): View
    {
        abort_unless($skill->subject_id === $subject->id, 404);

        return view('admin.skills.form', [
            'adminNav' => 'subjects',
            'subject' => $subject,
            'skill' => $skill,
            'total' => Skill::subjectTotalWeight($subject->id, $skill->id),
        ]);
    }

    public function update(UpdateSkillRequest $request, Subject $subject, Skill $skill): RedirectResponse
    {
        abort_unless($skill->subject_id === $subject->id, 404);

        $skill->update($request->validated());
        CatalogCache::flush();

        return redirect()
            ->route('admin.subjects.skills.index', $subject)
            ->with('success', 'Compétence mise à jour.');
    }

    public function destroy(Subject $subject, Skill $skill): RedirectResponse
    {
        abort_unless($skill->subject_id === $subject->id, 404);

        $skill->delete();
        CatalogCache::flush();

        return redirect()
            ->route('admin.subjects.skills.index', $subject)
            ->with('success', 'Compétence supprimée.');
    }
}
