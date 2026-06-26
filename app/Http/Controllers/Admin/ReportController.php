<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Report;
use App\Models\ReportPeriod;
use App\Models\Student;
use App\Services\BulletinGeneratorService;
use App\Services\BulletinPdfService;
use App\Services\BulletinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(BulletinService $bulletin): View
    {
        $period = ReportPeriod::active() ?? ReportPeriod::query()->orderBy('sort_order')->latest('id')->first();

        return view('admin.reports.index', [
            'adminNav' => 'reports',
            'period' => $period,
            'periods' => ReportPeriod::query()->orderBy('sort_order')->get(),
            'subjects' => $period ? $bulletin->subjectWeightsForPeriod($period) : [],
            'recentReports' => Report::with(['student', 'reportPeriod'])
                ->latest('generated_at')
                ->limit(15)
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reports.generate', [
            'adminNav' => 'reports',
            'periods' => ReportPeriod::query()->orderBy('sort_order')->get(),
            'students' => Student::with(['schoolLevel', 'classGroup'])->orderBy('last_name')->get(),
            'classes' => ClassGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BulletinGeneratorService $generator): RedirectResponse
    {
        $data = $request->validate([
            'report_period_id' => ['required', 'exists:report_periods,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $period = ReportPeriod::findOrFail($data['report_period_id']);
        $teacher = auth()->user();

        if (! empty($data['student_id'])) {
            $student = Student::findOrFail($data['student_id']);
            $report = $generator->generate($student, $period, $teacher, $data['comment'] ?? null);

            return redirect()
                ->route('admin.reports.show', $report)
                ->with('success', 'Bulletin généré pour '.$student->full_name.'.');
        }

        $reports = $generator->generateForClass(
            $period,
            $teacher,
            $data['class_group_id'] ?? null,
            $data['comment'] ?? null,
        );

        $count = count($reports);

        return redirect()
            ->route('admin.reports.index')
            ->with('success', $count.' bulletin'.($count > 1 ? 's' : '').' généré'.($count > 1 ? 's' : '').'.');
    }

    public function show(Report $report): View
    {
        $report->load(['student', 'reportPeriod', 'generatedBy']);

        return view('reports.show', [
            'layout' => 'layouts.admin',
            'section' => 'admin-content',
            'report' => $report,
            'payload' => $report->payload ?? [],
            'pdfUrl' => route('admin.reports.pdf', $report),
        ]);
    }

    public function pdf(Report $report, BulletinPdfService $pdf): StreamedResponse
    {
        $report->load('student');

        return $pdf->downloadResponse($report);
    }
}
