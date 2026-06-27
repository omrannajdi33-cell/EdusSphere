<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Answer;
use App\Models\Correction;
use App\Models\Progression;
use App\Services\ActivityCorrectionService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\PrivateStorage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        $activities = $student
            ? Activity::with(['subject', 'skill'])
                ->where('status', 'published')
                ->whereHas('assignedStudents', fn ($q) => $q->where('students.id', $student->id))
                ->latest('published_at')
                ->get()
            : collect();
        $progress = $student
            ? Progression::where('student_id', $student->id)->whereNotNull('activity_id')->get()->keyBy('activity_id')
            : collect();

        $corrections = $student
            ? Correction::query()
                ->where('student_id', $student->id)
                ->whereIn('activity_id', $activities->pluck('id'))
                ->get()
                ->keyBy('activity_id')
            : collect();

        return view('student.activities.index', [
            'activeNav' => 'activities',
            'activities' => $activities,
            'progress' => $progress,
            'corrections' => $corrections,
        ]);
    }

    public function play(Activity $activity): View
    {
        $student = auth()->user()->student;
        abort_unless($activity->isVisibleToStudent($student), 404);

        $activity->load(['pages.questions', 'pages.mediaFile', 'pages.audioMediaFile', 'subject', 'skill', 'lesson.mediaFiles']);

        $progression = $student
            ? Progression::firstOrCreate(
                ['student_id' => $student->id, 'activity_id' => $activity->id],
                ['last_page' => 1, 'percent_complete' => 0, 'workflow_status' => 'in_progress'],
            )
            : null;

        $answers = $student
            ? Answer::where('student_id', $student->id)
                ->whereIn('activity_page_id', $activity->pages->pluck('id'))
                ->get()
                ->groupBy('activity_page_id')
            : collect();

        $correction = $student
            ? Correction::where('activity_id', $activity->id)->where('student_id', $student->id)->first()
            : null;

        $lessonAnnotations = ($student && $activity->lesson)
            ? \App\Models\LessonAnnotation::query()
                ->where('student_id', $student->id)
                ->where('lesson_id', $activity->lesson_id)
                ->get()
                ->keyBy('media_file_id')
            : collect();

        return view('student.activities.player', [
            'activeNav' => 'activities',
            'activity' => $activity,
            'progression' => $progression,
            'answers' => $answers,
            'correction' => $correction,
            'lessonAnnotations' => $lessonAnnotations,
            'focusMode' => true,
        ]);
    }

    public function save(Request $request, Activity $activity): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($activity->isVisibleToStudent($student), 404);
        abort_unless($student, 403);

        $progression = Progression::where('student_id', $student->id)
            ->where('activity_id', $activity->id)
            ->first();

        abort_if($progression && in_array($progression->workflow_status, ['submitted', 'corrected'], true), 423);

        $data = $request->validate([
            'page_id' => ['required', 'exists:activity_pages,id'],
            'page_order' => ['required', 'integer', 'min:1'],
            'total_pages' => ['required', 'integer', 'min:1'],
            'responses' => ['nullable', 'array'],
            'canvas' => ['nullable', 'array'],
            'workspace' => ['nullable', 'array'],
        ]);

        $pageId = (int) $data['page_id'];
        abort_unless($activity->pages()->where('id', $pageId)->exists(), 404);

        foreach ($data['responses'] ?? [] as $questionId => $value) {
            Answer::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'question_id' => (int) $questionId,
                    'activity_page_id' => $pageId,
                ],
                ['content' => ['value' => $value]],
            );
        }

        if (isset($data['canvas']) || isset($data['workspace'])) {
            $existing = Answer::query()
                ->where('student_id', $student->id)
                ->where('question_id', null)
                ->where('activity_page_id', $pageId)
                ->first();

            $content = $existing?->content ?? [];

            if (isset($data['canvas'])) {
                $content['canvas'] = $data['canvas'];
            }
            if (isset($data['workspace'])) {
                $content['workspace'] = array_merge($content['workspace'] ?? [], $data['workspace']);
            }

            Answer::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'question_id' => null,
                    'activity_page_id' => $pageId,
                ],
                ['content' => $content],
            );
        }

        $percent = min(100, round(($data['page_order'] / $data['total_pages']) * 100, 2));

        Progression::updateOrCreate(
            ['student_id' => $student->id, 'activity_id' => $activity->id],
            [
                'last_page' => $data['page_order'],
                'percent_complete' => $percent,
            ],
        );

        return response()->json(['saved' => true, 'percent' => $percent]);
    }

    public function uploadRecording(Request $request, Activity $activity): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($activity->isVisibleToStudent($student), 404);
        abort_unless($student, 403);

        $data = $request->validate([
            'page_id' => ['required', 'exists:activity_pages,id'],
            'kind' => ['required', 'in:audio,video'],
            'recording' => ['required', 'file', 'max:51200'],
        ]);

        abort_unless($activity->pages()->where('id', $data['page_id'])->exists(), 404);

        $file = $request->file('recording');
        $ext = $file->getClientOriginalExtension() ?: 'webm';
        $path = $file->storeAs(
            'activities/'.$activity->id.'/students/'.$student->id,
            Str::uuid().'.'.$ext,
            PrivateStorage::DISK,
        );

        return response()->json([
            'path' => $path,
            'kind' => $data['kind'],
            'url' => route('activities.recording.show', [$activity, $student], absolute: false).'?path='.urlencode($path),
        ]);
    }

    public function showRecording(Request $request, Activity $activity)
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        return redirect()->to(
            route('activities.recording.show', [$activity, $student], absolute: false).'?path='.urlencode((string) $request->query('path', ''))
        );
    }

    public function submit(Activity $activity, ActivityCorrectionService $corrections): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($activity->isVisibleToStudent($student), 404);
        abort_unless($student, 403);

        $progression = Progression::where('student_id', $student->id)
            ->where('activity_id', $activity->id)
            ->first();

        abort_if(
            $progression && in_array($progression->workflow_status, ['submitted', 'corrected'], true),
            423,
        );

        Progression::updateOrCreate(
            ['student_id' => $student->id, 'activity_id' => $activity->id],
            [
                'workflow_status' => 'submitted',
                'submitted_at' => now(),
                'percent_complete' => 100,
            ],
        );

        $corrections->onSubmitted($activity, $student);

        app(NotificationService::class)->notifyTeachers('activity_submitted', [
            'activity_id' => $activity->id,
            'activity_title' => $activity->title,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'url' => route('admin.activities.corrections.show', [$activity, $student]),
        ]);

        return response()->json(['submitted' => true]);
    }
}
