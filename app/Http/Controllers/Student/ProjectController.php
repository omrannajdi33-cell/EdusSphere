<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\ProjectSubmissionFile;
use App\Services\ProjectCorrectionService;
use App\Support\PrivateStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        $projects = $student
            ? Project::with(['subject', 'submissions' => fn ($q) => $q->where('student_id', $student->id)])
                ->where('status', 'published')
                ->whereHas('assignedStudents', fn ($q) => $q->where('student_id', $student->id))
                ->latest('published_at')
                ->get()
            : collect();

        return view('student.projects.index', [
            'activeNav' => 'projects',
            'projects' => $projects,
            'student' => $student,
        ]);
    }

    public function work(Project $project): View
    {
        $student = auth()->user()->student;
        abort_unless($student && $project->isVisibleToStudent($student), 404);

        $submission = ProjectSubmission::firstOrCreate(
            ['project_id' => $project->id, 'student_id' => $student->id],
            ['workflow_status' => 'in_progress'],
        );

        $submission->load(['files', 'correction']);

        $project->load(['subject', 'attachments']);

        return view('student.projects.work', [
            'activeNav' => 'projects',
            'project' => $project,
            'submission' => $submission,
            'canEdit' => $submission->canEdit(),
        ]);
    }

    public function save(Request $request, Project $project): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $project->isVisibleToStudent($student), 404);

        $submission = ProjectSubmission::firstOrCreate(
            ['project_id' => $project->id, 'student_id' => $student->id],
            ['workflow_status' => 'in_progress'],
        );

        abort_if($submission->isLocked(), 423, 'Projet déjà soumis.');

        $data = $request->validate([
            'content' => ['nullable', 'string'],
            'research_notes' => ['nullable', 'string'],
            'sources' => ['nullable', 'array'],
            'sources.*.type' => ['nullable', 'string', 'max:32'],
            'sources.*.title' => ['nullable', 'string', 'max:255'],
            'sources.*.author' => ['nullable', 'string', 'max:255'],
            'sources.*.url' => ['nullable', 'string', 'max:500'],
            'sources.*.notes' => ['nullable', 'string', 'max:1000'],
            'sources.*.accessed_at' => ['nullable', 'string', 'max:32'],
            'bibliography' => ['nullable', 'array'],
            'bibliography.*.style' => ['nullable', 'string', 'max:32'],
            'bibliography.*.document_type' => ['nullable', 'string', 'max:32'],
            'bibliography.*.document_case' => ['nullable', 'string', 'max:64'],
            'bibliography.*.type' => ['nullable', 'string', 'max:32'],
            'bibliography.*.title' => ['nullable', 'string', 'max:255'],
            'bibliography.*.author' => ['nullable', 'string', 'max:255'],
            'bibliography.*.year' => ['nullable', 'string', 'max:16'],
            'bibliography.*.publisher' => ['nullable', 'string', 'max:255'],
            'bibliography.*.url' => ['nullable', 'string', 'max:500'],
            'bibliography.*.notes' => ['nullable', 'string', 'max:1000'],
            'bibliography.*.citation' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'content' => $data['content'] ?? $submission->content,
            'research_notes' => $data['research_notes'] ?? $submission->research_notes,
            'sources' => $this->cleanEntries($data['sources'] ?? []),
            'bibliography' => $this->cleanBibliographyEntries($data['bibliography'] ?? []),
            'last_saved_at' => now(),
        ]);

        return response()->json(['ok' => true, 'saved_at' => $submission->last_saved_at?->toIso8601String()]);
    }

    public function upload(Request $request, Project $project): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $project->isVisibleToStudent($student), 404);
        abort_unless($project->allowsUpload(), 422, 'Ce projet n\'accepte pas de fichiers.');

        $submission = ProjectSubmission::firstOrCreate(
            ['project_id' => $project->id, 'student_id' => $student->id],
            ['workflow_status' => 'in_progress'],
        );

        abort_if($submission->isLocked(), 423, 'Projet déjà soumis.');

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,ppt,pptx'],
            'label' => ['nullable', 'string', 'max:160'],
        ]);

        $file = $data['file'];
        $path = $file->storeAs(
            'projects/'.$project->id.'/students/'.$student->id,
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'private',
        );

        $record = ProjectSubmissionFile::create([
            'project_submission_id' => $submission->id,
            'filename' => $file->getClientOriginalName(),
            'label' => $data['label'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        $submission->update(['last_saved_at' => now()]);

        return response()->json([
            'ok' => true,
            'file' => [
                'id' => $record->id,
                'name' => $record->displayName(),
                'url' => route('project-submission-files.show', [$project, $record]),
            ],
        ]);
    }

    public function deleteFile(Project $project, ProjectSubmissionFile $submissionFile): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $project->isVisibleToStudent($student), 404);

        $submission = $submissionFile->submission;
        abort_unless($submission && $submission->project_id === $project->id, 404);
        abort_unless($submission->student_id === $student->id, 403);
        abort_if($submission->isLocked(), 423, 'Projet déjà soumis.');

        if ($submissionFile->path) {
            PrivateStorage::delete($submissionFile->path);
        }
        $submissionFile->delete();

        return response()->json(['ok' => true]);
    }

    public function submit(Project $project, ProjectCorrectionService $corrections): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $project->isVisibleToStudent($student), 404);

        $submission = ProjectSubmission::where('project_id', $project->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        abort_if($submission->isLocked(), 423, 'Projet déjà soumis.');

        if ($project->allowsOnlineWrite() && blank(trim(strip_tags($submission->content ?? ''))) && ! $project->allowsUpload()) {
            return response()->json(['message' => 'Rédige ton travail avant de soumettre.'], 422);
        }

        if ($project->allowsUpload() && ! $project->allowsOnlineWrite() && $submission->files()->count() === 0) {
            return response()->json(['message' => 'Dépose ton produit final avant de soumettre.'], 422);
        }

        if ($project->allowsUpload() && $project->allowsOnlineWrite()) {
            if (blank(trim(strip_tags($submission->content ?? '')))) {
                return response()->json(['message' => 'Rédige ton travail avant de soumettre.'], 422);
            }
            if (! $submission->files()->exists()) {
                return response()->json(['message' => 'Dépose ton produit final avant de soumettre.'], 422);
            }
        }

        if ($project->require_bibliography) {
            $hasCitation = collect($submission->bibliography ?? [])
                ->contains(fn ($entry) => filled(trim($entry['citation'] ?? '')));

            if (! $hasCitation) {
                return response()->json(['message' => 'Rédige au moins une note bibliographique selon le modèle.'], 422);
            }
        }

        $submission->update([
            'workflow_status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $corrections->onSubmitted($submission);

        return response()->json([
            'ok' => true,
            'url' => route('student.projects.index'),
        ]);
    }

    /** @param  array<int, array<string, mixed>>  $entries */
    private function cleanBibliographyEntries(array $entries): array
    {
        return collect($entries)
            ->filter(fn ($entry) => is_array($entry) && (
                filled(trim($entry['citation'] ?? '')) || filled(trim($entry['title'] ?? ''))
            ))
            ->values()
            ->all();
    }

    /** @param  array<int, array<string, mixed>>  $entries */
    private function cleanEntries(array $entries): array
    {
        return collect($entries)
            ->filter(fn ($entry) => is_array($entry) && filled(trim($entry['title'] ?? '')))
            ->values()
            ->all();
    }
}
