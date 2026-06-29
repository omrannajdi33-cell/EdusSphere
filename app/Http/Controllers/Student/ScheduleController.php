<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ScheduleGrid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request, ScheduleGrid $grid): View
    {
        $view = $request->string('view')->toString() ?: 'week';
        $view = in_array($view, ['day', 'week', 'month'], true) ? $view : 'week';

        $dateParam = $request->string('date')->toString();
        $reference = $dateParam !== '' ? Carbon::parse($dateParam) : now();

        $weekGrid = $grid->forWeek($reference, $request->user()?->student);
        $dayPeriods = $grid->forDay($reference, $request->user()?->student);
        $monthEvents = $grid->monthEventDays($reference->year, $reference->month, $request->user()?->student);

        return view('student.schedule.index', [
            'activeNav' => 'schedule',
            'view' => $view,
            'reference' => $reference,
            'weekGrid' => $weekGrid,
            'dayPeriods' => $dayPeriods,
            'periodDefs' => config('schedule.periods', []),
            'monthEvents' => $monthEvents,
            'month' => $reference->month,
            'year' => $reference->year,
            'prevDate' => match ($view) {
                'day' => $reference->copy()->subDay()->toDateString(),
                'month' => $reference->copy()->subMonth()->toDateString(),
                default => $weekGrid['week_start']->copy()->subWeek()->toDateString(),
            },
            'nextDate' => match ($view) {
                'day' => $reference->copy()->addDay()->toDateString(),
                'month' => $reference->copy()->addMonth()->toDateString(),
                default => $weekGrid['week_start']->copy()->addWeek()->toDateString(),
            },
        ]);
    }
}
