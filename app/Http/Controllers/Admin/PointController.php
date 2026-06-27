<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBehaviorPointRequest;
use App\Models\ClassGroup;
use App\Models\PointAction;
use App\Models\SchoolLevel;
use App\Models\Student;
use App\Services\BehaviorPointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::query()
            ->with(['user', 'schoolLevel', 'classGroup'])
            ->withSum('points as points_total', 'value')
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($levelId = $request->integer('level')) {
            $query->where('school_level_id', $levelId);
        }

        if ($classId = $request->integer('class')) {
            $query->where('class_group_id', $classId);
        }

        $positiveActions = PointAction::active()->positive()->orderBy('name')->get();
        $negativeActions = PointAction::active()->negative()->orderBy('name')->get();

        return view('admin.points.index', [
            'adminNav' => 'points',
            'students' => $query->get(),
            'levels' => SchoolLevel::orderBy('display_order')->get(),
            'classGroups' => ClassGroup::with('schoolLevel')->orderBy('name')->get(),
            'positiveActions' => $positiveActions,
            'negativeActions' => $negativeActions,
            'search' => $search ?? '',
            'levelFilter' => $levelId ?: null,
            'classFilter' => $classId ?: null,
        ]);
    }

    public function store(StoreBehaviorPointRequest $request, BehaviorPointService $points): JsonResponse|RedirectResponse
    {
        $student = Student::findOrFail($request->integer('student_id'));
        $action = PointAction::active()->findOrFail($request->integer('point_action_id'));

        $points->award($student, $action, $request->user(), $request->string('note')->trim()->toString() ?: null);

        $total = $points->totalFor($student);
        $sign = $action->value > 0 ? '+' : '';
        $message = "{$sign}{$action->value} pt · {$action->name} pour {$student->first_name}";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'total' => $total,
                'student_id' => $student->id,
            ]);
        }

        return back()->with('success', $message);
    }
}
