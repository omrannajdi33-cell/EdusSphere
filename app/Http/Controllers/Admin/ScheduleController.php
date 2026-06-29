<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Activity;
use App\Models\Exam;
use App\Models\Notion;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ScheduleGrid;
use App\Services\SchedulePlannerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        private SchedulePlannerService $planner,
    ) {}

    public function index(Request $request, ScheduleGrid $grid): View
    {
        $weekParam = $request->string('week')->toString();
        $reference = $weekParam !== ''
            ? Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        return view('admin.schedules.index', [
            'adminNav' => 'schedules',
            'grid' => $grid->forWeek($reference),
            'subjects' => Subject::ordered()->get(),
            'dayLabels' => config('schedule.day_labels', []),
            'linkableActivities' => Activity::query()
                ->with('subject')
                ->where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title', 'subject_id']),
            'linkableExams' => Exam::query()
                ->with('subject')
                ->where('status', '!=', 'draft')
                ->orderBy('title')
                ->get(['id', 'title', 'subject_id']),
            'linkableProjects' => Project::query()
                ->with('subject')
                ->where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title', 'subject_id']),
            'linkableNotions' => Notion::query()
                ->with(['category', 'subject'])
                ->orderBy('subject_id')
                ->orderBy('title')
                ->get(['id', 'title', 'subject_id', 'notion_category_id']),
            'students' => Student::query()
                ->with('user')
                ->whereHas('user', fn ($q) => $q->where('status', 'active'))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'prevWeek' => $reference->copy()->subWeek()->toDateString(),
            'nextWeek' => $reference->copy()->addWeek()->toDateString(),
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $data = $this->resolveSlotData($validated);
        $this->removeConflictingSlot($data);

        $schedule = Schedule::create($data);
        $this->planner->sync($schedule, $validated);

        return $this->redirectBack($request, 'Créneau ajouté à l\'horaire.');
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validated();
        $data = $this->resolveSlotData($validated);
        $this->removeConflictingSlot($data, $schedule->id);
        $schedule->update($data);
        $this->planner->sync($schedule, $validated);

        return $this->redirectBack($request, 'Créneau mis à jour.');
    }

    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return $this->redirectBack($request, 'Créneau supprimé.');
    }

    /** @param  array<string, mixed>  $validated */
    private function resolveSlotData(array $validated): array
    {
        $periodNumber = (int) $validated['period_number'];
        $defaults = Schedule::defaultTimesForPeriod($periodNumber);
        $subject = Subject::findOrFail($validated['subject_id']);
        $isRecurring = $validated['mode'] === 'recurring';
        $useCustomTime = (bool) ($validated['use_custom_time'] ?? false);

        return [
            'subject_id' => $subject->id,
            'title' => $validated['title'] ?: $subject->name,
            'color' => $validated['color'] ?? $subject->color,
            'period_number' => $periodNumber,
            'day_of_week' => $isRecurring
                ? (int) $validated['day_of_week']
                : Carbon::parse($validated['schedule_date'])->dayOfWeekIso,
            'starts_at' => $useCustomTime ? $validated['starts_at'] : $defaults['starts_at'],
            'ends_at' => $useCustomTime ? $validated['ends_at'] : $defaults['ends_at'],
            'uses_custom_time' => $useCustomTime,
            'schedule_date' => $isRecurring ? null : $validated['schedule_date'],
            'materials' => $validated['materials'] ?? null,
            'plan' => $validated['plan'] ?? null,
        ];
    }

    /** @param  array<string, mixed>  $data */
    private function removeConflictingSlot(array $data, ?int $exceptId = null): void
    {
        $query = Schedule::query()->where('period_number', $data['period_number']);

        if ($data['schedule_date']) {
            $query->whereDate('schedule_date', $data['schedule_date']);
        } else {
            $query->whereNull('schedule_date')->where('day_of_week', $data['day_of_week']);
        }

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->delete();
    }

    private function redirectBack(Request $request, string $message): RedirectResponse
    {
        $week = $request->input('week', now()->startOfWeek(Carbon::MONDAY)->toDateString());

        return redirect()
            ->route('admin.schedules.index', ['week' => $week])
            ->with('success', $message);
    }
}
