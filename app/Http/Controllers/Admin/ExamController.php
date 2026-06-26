<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Models\Exam;
use App\Models\ReportPeriod;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        return view('admin.exams.index', [
            'adminNav' => 'exams',
            'exams' => Exam::with(['subject', 'skill', 'reportPeriod'])
                ->withCount('pages')
                ->latest('opens_at')
                ->paginate(12),
        ]);
    }

    public function create(): RedirectResponse
    {
        $period = ReportPeriod::active();

        $exam = Exam::create([
            'subject_id' => Subject::ordered()->value('id'),
            'skill_id' => Skill::orderBy('name')->value('id'),
            'report_period_id' => $period?->id,
            'title' => 'Nouvel examen',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'weight_percent' => 0,
            'opens_at' => now()->addDay(),
            'closes_at' => now()->addDay()->addHours(2),
            'status' => 'draft',
        ]);

        return redirect()->route('admin.exams.build', $exam);
    }

    public function build(Exam $exam): View
    {
        $exam->load(['pages.questions', 'subject', 'skill', 'reportPeriod']);

        return view('admin.exams.build', [
            'adminNav' => 'exams',
            'exam' => $exam,
            'pageTypes' => collect(config('activity.page_types', []))->only(['interactive', 'free_write']),
            'questionTypes' => config('activity.question_types', []),
            'subjects' => Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get()),
            'skills' => Cache::remember('catalog.skills', 3600, fn () => Skill::orderBy('name')->get()),
            'periods' => ReportPeriod::query()->latest('id')->get(),
        ]);
    }

    public function edit(Exam $exam): RedirectResponse
    {
        return redirect()->route('admin.exams.build', $exam);
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->update($request->validated());

        return redirect()
            ->route('admin.exams.build', $exam)
            ->with('success', 'Examen enregistré.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Examen supprimé.');
    }

    public function open(Exam $exam): RedirectResponse
    {
        abort_unless($exam->contentReady(), 422, 'Ajoute du contenu avant d\'ouvrir l\'examen.');

        $exam->update(['status' => 'open']);

        return back()->with('success', 'Examen ouvert aux élèves.');
    }

    public function close(Exam $exam): RedirectResponse
    {
        $exam->update(['status' => 'closed']);

        return back()->with('success', 'Examen fermé.');
    }
}
