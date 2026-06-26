@extends($layout ?? 'layouts.admin')

@section($section ?? 'admin-content')
    @include('reports.partials.body', [
        'payload' => $payload,
        'report' => $report,
        'pdfUrl' => $pdfUrl ?? null,
    ])
@endsection
