<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletin — {{ $payload['student']['full_name'] ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; margin: 24px; line-height: 1.4; }
        .bulletin-pdf-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #0f172a; text-align: center; }
        .bulletin-pdf-header h2 { font-size: 20px; margin: 4px 0; }
        .bulletin-pdf-meta { margin-bottom: 20px; border: 1px solid #e7e5e4; padding: 12px; }
        .bulletin-pdf-meta p { margin: 2px 0; }
        .bulletin-pdf-subject { page-break-inside: avoid; margin-bottom: 18px; }
        .bulletin-pdf-subject h3 { font-size: 14px; border-bottom: 1px solid #d6d3d1; padding-bottom: 4px; margin-bottom: 10px; }
        .bulletin-pdf-subject h4 { font-size: 12px; margin: 8px 0 6px; color: #334155; }
        .bulletin-pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .bulletin-pdf-table th, .bulletin-pdf-table td { border: 1px solid #d6d3d1; padding: 6px; vertical-align: top; }
        .bulletin-pdf-table th { background: #f5f5f4; font-weight: bold; }
        .text-muted { color: #64748b; }
        .text-primary { color: #4f46e5; font-weight: bold; }
        ul { margin: 0; padding-left: 0; list-style: none; }
        li { margin-bottom: 3px; }
    </style>
</head>
<body>
    @include('reports.partials.body', ['forPdf' => true])
</body>
</html>
