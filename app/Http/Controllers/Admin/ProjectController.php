<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublishProjectRequest;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\ClassGroup;
use App\Models\Project;
use App\Models\MediaFile;
use App\Models\ProjectSubmission;
use App\Models\ReportPeriod;
use App\Models\SchoolLevel;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ProjectCorrectionService;
use App\Services\ProjectDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with(['subject', 'skill', 'skills', 'reportPeriod'])->withCount('submissions')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($subjectId = $request->integer('subject')) {
            $query->where('subject_id', $subjectId);
        }

        return view('admin.projects.index', [
            'adminNav' => 'projects',
            'projects' => $query->paginate(12)->withQueryString(),
            'subjects' => Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get()),
            'statusFilter' => $status ?: null,
            'subjectFilter' => $subjectId ?: null,
        ]);
    }

    public function create(): View
    {
        return $this->wizardView(new Project, 1);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $skillIds = $data['skill_ids'];
        $skillWeights = $data['skill_weights'] ?? [];
        unset($data['skill_ids'], $data['skill_weights']);

        $project = Project::create([
            ...$data,
            'skill_id' => $skillIds[0],
            'created_by' => $request->user()->id,
            'status' => 'draft',
        ]);

        $this->syncProjectSkills($project, $skillIds, $skillWeights);

        return redirect()
            ->route('admin.projects.build', ['project' => $project, 'step' => 2])
            ->with('success', 'Étape 1 terminée. Rédige maintenant les consignes.');
    }

    public function build(Request $request, Project $project): View|RedirectResponse
    {
        $step = max(1, min(3, (int) $request->query('step', 2)));

        if ($step === 1) {
            return $this->wizardView($project, 1);
        }

        if ($step === 3 && blank($project->instructions)) {
            return redirect()
                ->route('admin.projects.build', ['project' => $project, 'step' => 2])
                ->withErrors(['instructions' => 'Ajoute des consignes avant de publier.']);
        }

        return $this->wizardView($project->load(['attachments', 'subject', 'skill', 'skills', 'reportPeriod', 'assignedStudents']), $step);
    }

    public function edit(Project $project): RedirectResponse
    {
        return redirect()->route('admin.projects.build', ['project' => $project, 'step' => 1]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        $skillIds = $data['skill_ids'];
        $skillWeights = $data['skill_weights'] ?? [];
        unset($data['skill_ids'], $data['skill_weights']);

        $project->update([
            ...$data,
            'skill_id' => $skillIds[0],
        ]);

        $this->syncProjectSkills($project, $skillIds, $skillWeights);

        $nextStep = (int) $request->input('next_step', 2);

        return redirect()
            ->route('admin.projects.build', ['project' => $project, 'step' => $nextStep])
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Projet supprimé.');
    }

    public function publish(PublishProjectRequest $request, Project $project): RedirectResponse
    {
        $project->publishTo($request->validated('student_ids'));

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Projet publié aux élèves sélectionnés.');
    }

    public function unpublish(Project $project): RedirectResponse
    {
        $project->update(['status' => 'draft', 'published_at' => null]);

        return back()->with('success', 'Projet remis en brouillon.');
    }

    public function storeAttachment(Request $request, Project $project, ProjectDocumentService $documents): RedirectResponse
    {
        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:160'],
        ]);

        foreach ($data['documents'] as $index => $file) {
            $documents->store($project, $file, $data['labels'][$index] ?? null);
        }

        return back()->with('success', 'Pièce(s) jointe(s) ajoutée(s).');
    }

    public function destroyAttachment(Project $project, MediaFile $media, ProjectDocumentService $documents): RedirectResponse
    {
        abort_unless($media->project_id === $project->id, 404);
        $documents->delete($media);

        return back()->with('success', 'Pièce jointe supprimée.');
    }

    public function submissions(Project $project): View
    {
        $submissions = ProjectSubmission::with(['student.user', 'correction'])
            ->where('project_id', $project->id)
            ->latest('updated_at')
            ->get();

        return view('admin.projects.submissions', [
            'adminNav' => 'projects',
            'project' => $project->load('subject'),
            'submissions' => $submissions,
        ]);
    }

    public function correct(Project $project, Student $student): View
    {
        $submission = ProjectSubmission::with(['files', 'correction.history.user', 'student'])
            ->where('project_id', $project->id)
            ->where('student_id', $student->id)
            ->whereIn('workflow_status', ['submitted', 'corrected', 'returned'])
            ->firstOrFail();

        return view('admin.projects.correct', [
            'adminNav' => 'corrections',
            'project' => $project->load(['subject', 'attachments']),
            'student' => $student,
            'submission' => $submission,
            'correction' => $submission->correction,
        ]);
    }

    public function finalizeCorrection(Request $request, Project $project, Student $student, ProjectCorrectionService $corrections): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string'],
        ]);

        $submission = ProjectSubmission::where('project_id', $project->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $corrections->finalize($submission, $request->user(), (float) $data['score'], $data['comment'] ?? null);

        return redirect()->route('admin.corrections.index')->with('success', 'Correction validée.');
    }

    public function returnSubmission(Request $request, Project $project, Student $student, ProjectCorrectionService $corrections): RedirectResponse
    {
        $data = $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $submission = ProjectSubmission::where('project_id', $project->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $corrections->returnToStudent($submission, $request->user(), $data['comment']);

        return back()->with('success', 'Projet renvoyé à l\'élève.');
    }

    protected function wizardView(Project $project, int $step): View
    {
        $subjects = Cache::remember('catalog.subjects', 3600, fn () => Subject::ordered()->get());
        $skills = Cache::remember('catalog.skills', 3600, fn () => Skill::orderBy('subject_id')->orderBy('name')->get());

        $students = Student::with(['schoolLevel', 'classGroup'])
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $levels = SchoolLevel::withCount('students')->orderBy('display_order')->get();
        $classGroups = ClassGroup::withCount('students')->orderBy('name')->get();

        return view('admin.projects.build', [
            'adminNav' => 'projects',
            'project' => $project->loadMissing(['skills', 'reportPeriod']),
            'step' => $step,
            'subjects' => $subjects,
            'skills' => $skills,
            'periods' => ReportPeriod::query()->orderBy('sort_order')->orderBy('id')->get(),
            'students' => $students,
            'levels' => $levels,
            'classGroups' => $classGroups,
            'selectedStudentIds' => $project->exists ? $project->assignedStudents()->pluck('students.id')->all() : [],
            'selectedSkillIds' => old('skill_ids', $project->exists ? $project->skills->pluck('id')->all() : []),
        ]);
    }

    /** @param  list<int>  $skillIds  @param  array<int|string, float|int|string|null>  $skillWeights */
    private function syncProjectSkills(Project $project, array $skillIds, array $skillWeights): void
    {
        $skillIds = array_values(array_unique(array_map('intval', $skillIds)));
        $sync = [];
        $hasCustomWeights = collect($skillIds)->every(fn (int $id) => isset($skillWeights[$id]) && $skillWeights[$id] !== '');

        if ($hasCustomWeights) {
            foreach ($skillIds as $id) {
                $sync[$id] = ['weight_percent' => round((float) $skillWeights[$id], 2)];
            }
        } else {
            $share = round(100 / count($skillIds), 2);
            $assigned = 0.0;

            foreach ($skillIds as $index => $id) {
                $weight = $index === count($skillIds) - 1
                    ? round(100 - $assigned, 2)
                    : $share;
                $sync[$id] = ['weight_percent' => $weight];
                $assigned += $weight;
            }
        }

        $project->skills()->sync($sync);
    }
}
