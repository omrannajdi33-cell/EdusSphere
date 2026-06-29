<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublishActivityRequest;
use App\Http\Requests\Admin\StoreActivityRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\ClassGroup;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Support\SubjectWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['subject', 'skill'])->withCount('pages')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($subjectId = $request->integer('subject')) {
            $query->where('subject_id', $subjectId);
        }

        if ($deviceType = $request->string('device')->toString()) {
            $query->where('device_type', $deviceType);
        }

        return view('admin.activities.index', [
            'adminNav' => 'activities',
            'activities' => $query->paginate(12)->withQueryString(),
            'subjects' => Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get()),
            'statusFilter' => $status ?: null,
            'subjectFilter' => $subjectId ?: null,
            'deviceFilter' => $deviceType ?: null,
        ]);
    }

    public function create(): View
    {
        return $this->wizardView(new Activity, 1);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $activity = Activity::create([
            ...$request->validated(),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('admin.activities.build', ['activity' => $activity, 'step' => 2])
            ->with('success', 'Étape 1 terminée. Choisis maintenant le contenu de ton activité.');
    }

    public function build(Request $request, Activity $activity): View|RedirectResponse
    {
        $step = (int) $request->query('step', 2);
        $step = max(1, min(3, $step));

        if ($step === 1) {
            return $this->wizardView($activity, 1);
        }

        if ($step === 3 && $activity->pages()->count() === 0) {
            return redirect()
                ->route('admin.activities.build', ['activity' => $activity, 'step' => 2])
                ->withErrors(['steps' => 'Ajoute au moins une étape avant de publier.']);
        }

        return $this->wizardView($activity->load(['pages.questions', 'pages.mediaFile', 'subject', 'skill']), $step);
    }

    public function edit(Activity $activity): RedirectResponse
    {
        return redirect()->route('admin.activities.build', ['activity' => $activity, 'step' => 1]);
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        $activity->update($request->validated());

        return redirect()
            ->route('admin.activities.build', ['activity' => $activity, 'step' => 2])
            ->with('success', 'Informations mises à jour.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activité supprimée.');
    }

    public function publish(PublishActivityRequest $request, Activity $activity): RedirectResponse
    {
        if ($activity->pages()->count() === 0) {
            return back()->withErrors(['publish' => 'Ajoute au moins une étape avant de publier.']);
        }

        $studentIds = collect($request->validated('student_ids'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $validCount = Student::query()
            ->whereIn('id', $studentIds)
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->count();

        if ($validCount !== count($studentIds)) {
            return back()->withErrors(['student_ids' => 'Un ou plusieurs élèves sélectionnés ne sont pas actifs.']);
        }

        $alreadyPublished = $activity->isPublished();
        $activity->publishTo($studentIds);

        $count = count($studentIds);
        $message = $alreadyPublished
            ? "Destinataires mis à jour ({$count} élève(s))."
            : "Activité publiée pour {$count} élève(s) !";

        return redirect()
            ->route('admin.activities.build', ['activity' => $activity, 'step' => 3])
            ->with('success', $message);
    }

    public function unpublish(Activity $activity): RedirectResponse
    {
        $activity->unpublish();

        return back()->with('success', 'Activité dépubliée.');
    }

    public function preview(Activity $activity): View
    {
        $activity->load(['pages.questions', 'pages.mediaFile', 'subject', 'skill']);

        return view('admin.activities.preview', [
            'adminNav' => 'activities',
            'activity' => $activity,
            'previewMode' => true,
        ]);
    }

    protected function wizardView(Activity $activity, int $step): View
    {
        if ($activity->exists) {
            $activity->loadMissing(['pages.questions', 'pages.mediaFile', 'pages.audioMediaFile', 'subject', 'skill', 'lesson.mediaFiles']);
        }

        $viewData = [
            'adminNav' => 'activities',
            'activity' => $activity,
            'step' => $step,
            'pageTypes' => SubjectWorkspace::pageTypesForSubject($activity->subject),
            'subjectWorkspace' => SubjectWorkspace::forSubject($activity->subject),
            'questionTypes' => config('activity.question_types', []),
        ];

        if ($step === 1) {
            $viewData['subjects'] = Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get());
            $viewData['skills'] = Cache::remember('catalog.skills', 3600, fn () => Skill::orderBy('name')->get());
            $viewData['lessons'] = \App\Models\Lesson::query()
                ->where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title', 'subject_id']);
        }

        if ($step === 3) {
            $viewData['students'] = Student::with(['user', 'schoolLevel', 'classGroup'])
                ->whereHas('user', fn ($q) => $q->where('status', 'active'))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
            $viewData['levels'] = SchoolLevel::withCount([
                'students' => fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', 'active')),
            ])->orderBy('display_order')->get();
            $viewData['classGroups'] = ClassGroup::with('schoolLevel')
                ->withCount([
                    'students' => fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', 'active')),
                ])
                ->orderBy('name')
                ->get();
            $viewData['assignedStudentIds'] = $activity->exists
                ? $activity->assignedStudents()->pluck('students.id')->all()
                : [];
        }

        return view('admin.activities.build', $viewData);
    }
}
