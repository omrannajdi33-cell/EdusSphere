@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="es-page-title">Mon bulletin</h1>
            <p class="es-page-subtitle">Notes officielles par matière et compétence</p>
        </div>
        @if ($latestReport)
            <x-button href="{{ route('student.bulletin.pdf', $latestReport) }}" variant="secondary">Télécharger PDF</x-button>
        @endif
    </div>

    @if ($generatedReports->isNotEmpty())
        <div class="mb-8 flex flex-wrap gap-2">
            @foreach ($generatedReports as $genReport)
                <a href="{{ route('student.bulletin.show', $genReport) }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ $loop->first ? 'bg-es-primary text-white' : 'bg-white border border-stone-200 text-es-ink hover:border-es-primary' }}">
                    {{ $genReport->period_label }}
                    @if ($genReport->general_average)
                        · {{ number_format($genReport->general_average, 0) }}/100
                    @endif
                </a>
            @endforeach
        </div>

        @if ($latestReport?->payload)
            <div class="mb-10">
                @include('reports.partials.body', [
                    'payload' => $latestReport->payload,
                    'report' => $latestReport,
                    'pdfUrl' => route('student.bulletin.pdf', $latestReport),
                ])
            </div>
        @endif
    @else
        <div class="es-empty mb-10">
            <p class="font-extrabold">Bulletin pas encore publié</p>
            <p class="text-es-muted mt-2">Ton professeur générera le bulletin à la fin de chaque période.</p>
        </div>

        @if (! empty($subjects))
            <h2 class="es-section-title">Progression en cours</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($subjects as $card)
                    @php
                        $subject = $card['subject'];
                        $done = $card['completed_weight'];
                        $missing = $card['missing_weight'];
                        $grade = $card['final_grade'] ?? $card['provisional_grade'];
                    @endphp
                    <article class="es-card p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <x-subject-icon :icon="$subject->icon" :color="$subject->color" size="sm"/>
                            <div>
                                <h2 class="font-extrabold text-lg">{{ $subject->name }}</h2>
                                @if ($grade !== null)
                                    <p class="text-sm font-bold text-es-primary">Note provisoire : {{ number_format($grade, 0) }}/100</p>
                                @endif
                            </div>
                        </div>
                        <x-circular-progress
                            :percent="$done"
                            :color="$subject->color"
                            label="Examens complétés"
                            :sublabel="$missing > 0 ? 'Encore '.$missing.'% à passer' : 'Complet'"
                        />
                    </article>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
