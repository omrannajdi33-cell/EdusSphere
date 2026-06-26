@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-8 flex flex-wrap justify-between gap-4">
        <div>
            <h1 class="es-page-title">Bulletin</h1>
            <p class="es-page-subtitle">
                @if ($period)
                    {{ $period->label }} — {{ $period->school_year }}
                @else
                    Aucune période active
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-button href="{{ route('admin.reports.generate') }}">Générer le bulletin</x-button>
            <x-button href="{{ route('admin.exams.create') }}" variant="secondary">+ Nouvel examen</x-button>
        </div>
    </div>

    @if ($period)
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 mb-10">
            @foreach ($subjects as $card)
                @php
                    $subject = $card['subject'];
                    $filled = $card['total_weight'];
                    $missing = $card['missing_weight'];
                @endphp
                <article class="es-card p-6 flex flex-col items-center text-center">
                    <x-subject-icon :icon="$subject->icon" :color="$subject->color" size="md" class="mb-3"/>
                    <h2 class="font-extrabold text-lg">{{ $subject->name }}</h2>
                    <x-circular-progress
                        :percent="$filled"
                        :color="$subject->color"
                        :label="$filled >= 99.99 ? 'Poids complet' : 'Poids défini'"
                        :sublabel="$missing > 0 ? 'Il manque '.$missing.'% à répartir' : '100% répartis entre les examens'"
                        class="my-4"
                    />
                </article>
            @endforeach
        </div>
    @endif

    @if ($recentReports->isNotEmpty())
        <x-card>
            <h2 class="text-lg font-extrabold mb-4">Bulletins générés récemment</h2>
            <ul class="divide-y divide-stone-100">
                @foreach ($recentReports as $report)
                    <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-bold">{{ $report->student->full_name }}</p>
                            <p class="text-sm text-es-muted">
                                {{ $report->period_label }}
                                @if ($report->general_average)
                                    · {{ number_format($report->general_average, 1) }}/100
                                @endif
                                · {{ $report->generated_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <x-button href="{{ route('admin.reports.show', $report) }}" variant="secondary" class="es-btn-sm">Voir</x-button>
                            <x-button href="{{ route('admin.reports.pdf', $report) }}" variant="secondary" class="es-btn-sm">PDF</x-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @else
        <div class="es-empty mt-8">
            <p class="font-extrabold">Aucun bulletin généré</p>
            <p class="text-es-muted mt-2">Clique sur « Générer le bulletin » pour créer les bulletins PDF et web.</p>
        </div>
    @endif
</div>
@endsection
