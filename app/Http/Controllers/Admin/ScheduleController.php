<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\Subject;
use App\Services\ScheduleGrid;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
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
            'upcomingDates' => $grid->upcomingSpecificDates(),
            'prevWeek' => $reference->copy()->subWeek()->toDateString(),
            'nextWeek' => $reference->copy()->addWeek()->toDateString(),
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $data = $this->resolveSlotData($request->validated());
        $this->removeConflictingSlot($data);

        Schedule::create($data);

        return $this->redirectBack($request, 'Créneau ajouté à l\'horaire.');
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $data = $this->resolveSlotData($request->validated());
        $this->removeConflictingSlot($data, $schedule->id);
        $schedule->update($data);

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
        $period = config('schedule.periods.'.$validated['period_number'], []);
        $subject = Subject::findOrFail($validated['subject_id']);

        $isRecurring = $validated['mode'] === 'recurring';

        return [
            'subject_id' => $subject->id,
            'title' => $validated['title'] ?: $subject->name,
            'color' => $validated['color'] ?? $subject->color,
            'period_number' => (int) $validated['period_number'],
            'day_of_week' => $isRecurring
                ? (int) $validated['day_of_week']
                : Carbon::parse($validated['schedule_date'])->dayOfWeekIso,
            'starts_at' => $period['starts_at'] ?? '08:00',
            'ends_at' => $period['ends_at'] ?? '09:00',
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
