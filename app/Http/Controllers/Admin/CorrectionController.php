<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Correction;
use Illuminate\View\View;

class CorrectionController extends Controller
{
    public function index(): View
    {
        $activityCorrections = Correction::query()
            ->with(['student.user', 'activity.subject'])
            ->whereNotNull('activity_id')
            ->whereIn('status', ['to_correct', 'submitted'])
            ->latest('updated_at')
            ->get();

        $examCorrections = Correction::query()
            ->with(['student.user', 'examAttempt.exam.subject'])
            ->whereNotNull('exam_attempt_id')
            ->whereIn('status', ['to_correct', 'submitted'])
            ->latest('updated_at')
            ->get();

        return view('admin.corrections.index', [
            'adminNav' => 'corrections',
            'activityCorrections' => $activityCorrections,
            'examCorrections' => $examCorrections,
        ]);
    }
}
