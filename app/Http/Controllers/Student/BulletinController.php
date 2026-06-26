<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportPeriod;
use App\Services\BulletinPdfService;
use App\Services\BulletinService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulletinController extends Controller
{
    public function index(BulletinService $bulletin): View
    {
        $student = auth()->user()->student;
        $period = ReportPeriod::active() ?? ReportPeriod::query()->orderBy('sort_order')->latest('id')->first();

        $generatedReports = $student
            ? Report::query()
                ->where('student_id', $student->id)
                ->with('reportPeriod')
                ->latest('generated_at')
                ->get()
            : collect();

        $latestReport = $generatedReports->first();

        return view('student.bulletin.index', [
            'activeNav' => 'bulletin',
            'period' => $period,
            'periods' => ReportPeriod::query()->orderBy('sort_order')->get(),
            'subjects' => $student && $period ? $bulletin->subjectsForStudent($student, $period) : [],
            'generatedReports' => $generatedReports,
            'latestReport' => $latestReport,
        ]);
    }

    public function show(Report $report): View
    {
        $student = auth()->user()->student;
        abort_unless($student && $report->student_id === $student->id, 403);

        $report->load(['reportPeriod', 'generatedBy']);

        return view('reports.show', [
            'layout' => 'layouts.student',
            'section' => 'student-content',
            'report' => $report,
            'payload' => $report->payload ?? [],
            'pdfUrl' => route('student.bulletin.pdf', $report),
        ]);
    }

    public function pdf(Report $report, BulletinPdfService $pdf): StreamedResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $report->student_id === $student->id, 403);

        return $pdf->downloadResponse($report);
    }
}
