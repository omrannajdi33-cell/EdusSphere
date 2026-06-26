<?php

namespace App\Services;

use App\Models\Report;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class BulletinPdfService
{
    /** @param  array<string, mixed>  $payload */
    public function store(Report $report, array $payload): string
    {
        $html = view('reports.pdf', [
            'payload' => $payload,
            'report' => $report,
            'forPdf' => true,
        ])->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $path = sprintf(
            'reports/%d/%d/bulletin-%d.pdf',
            $report->student_id,
            $report->report_period_id ?? 0,
            $report->id ?: time(),
        );

        Storage::disk('local')->put($path, $dompdf->output());

        return $path;
    }

    public function downloadResponse(Report $report)
    {
        abort_unless($report->pdf_path && Storage::disk('local')->exists($report->pdf_path), 404);

        $filename = sprintf(
            'bulletin-%s-%s.pdf',
            str($report->student->full_name)->slug(),
            str($report->period_label)->slug(),
        );

        return Storage::disk('local')->download($report->pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
