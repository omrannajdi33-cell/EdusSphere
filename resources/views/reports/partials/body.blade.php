@php
    $payload = $payload ?? $report->payload ?? [];
    $student = $payload['student'] ?? [];
    $periods = collect($payload['included_periods'] ?? []);
    $subjects = $payload['subjects'] ?? [];
    $forPdf = $forPdf ?? false;
@endphp

<div class="{{ $forPdf ? '' : 'es-page-enter max-w-4xl mx-auto' }}">
    @unless ($forPdf)
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="es-page-title">Bulletin scolaire</h1>
                <p class="es-page-subtitle">
                    {{ $payload['school_year'] ?? '' }} · {{ $payload['period']['label'] ?? $report->period_label ?? '' }}
                </p>
            </div>
            @if (isset($report) && $report->pdf_path)
                <x-button href="{{ $pdfUrl ?? route('admin.reports.pdf', $report) }}" variant="secondary">Télécharger PDF</x-button>
            @endif
        </div>
    @endunless

    <div class="{{ $forPdf ? 'bulletin-pdf' : 'es-card p-6 md:p-8' }}">
        {{-- En-tête --}}
        <header class="text-center border-b-2 border-es-ink pb-5 mb-6 {{ $forPdf ? 'bulletin-pdf-header' : '' }}">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-es-muted">EduSphere</p>
            <h2 class="text-2xl font-black text-es-ink mt-1">Bulletin de notes</h2>
            <p class="text-sm font-semibold text-es-muted mt-1">Année scolaire {{ $payload['school_year'] ?? '—' }}</p>
            <p class="text-lg font-extrabold text-es-primary mt-2">{{ $payload['period']['label'] ?? '' }}</p>
        </header>

        {{-- Élève --}}
        <div class="grid sm:grid-cols-2 gap-4 mb-8 {{ $forPdf ? 'bulletin-pdf-meta' : 'rounded-2xl bg-stone-50 p-4' }}">
            <div>
                <p class="text-xs font-bold uppercase text-es-muted">Élève</p>
                <p class="font-extrabold text-lg">{{ $student['full_name'] ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase text-es-muted">Classe</p>
                <p class="font-semibold">
                    {{ $student['school_level'] ?? '—' }}
                    @if (! empty($student['class_group']))
                        · {{ $student['class_group'] }}
                    @endif
                </p>
            </div>
            @if (! empty($student['birth_date']))
                <div>
                    <p class="text-xs font-bold uppercase text-es-muted">Date de naissance</p>
                    <p class="font-semibold">{{ $student['birth_date'] }}</p>
                </div>
            @endif
            @if (($payload['general_average'] ?? null) !== null)
                <div>
                    <p class="text-xs font-bold uppercase text-es-muted">Moyenne générale</p>
                    <p class="text-2xl font-black text-es-primary">{{ number_format($payload['general_average'], 1) }}/100</p>
                </div>
            @endif
        </div>

        {{-- Matières & compétences --}}
        @forelse ($subjects as $subject)
            <section class="mb-8 {{ $forPdf ? 'bulletin-pdf-subject' : '' }}">
                <div class="flex flex-wrap items-center gap-3 mb-4 pb-2 border-b border-stone-200">
                    @unless ($forPdf)
                        <x-subject-icon :icon="$subject['icon']" :color="$subject['color']" size="sm"/>
                    @endunless
                    <h3 class="text-xl font-extrabold text-es-ink flex-1">{{ $subject['name'] }}</h3>
                    @if (($subject['average'] ?? null) !== null)
                        <span class="text-sm font-black text-es-primary">Moyenne : {{ number_format($subject['average'], 1) }}/100</span>
                    @endif
                </div>

                @foreach ($subject['skills'] ?? [] as $skill)
                    <div class="mb-5 {{ $forPdf ? '' : 'ml-0 md:ml-4' }}">
                        <h4 class="font-bold text-es-ink mb-3">{{ $skill['name'] }}</h4>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse {{ $forPdf ? 'bulletin-pdf-table' : '' }}">
                                <thead>
                                    <tr class="bg-stone-100 text-left">
                                        <th class="p-2 font-bold border border-stone-200">Période</th>
                                        <th class="p-2 font-bold border border-stone-200">Examens & notes</th>
                                        <th class="p-2 font-bold border border-stone-200 w-24 text-center">Moyenne</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($skill['periods'] ?? [] as $block)
                                        <tr>
                                            <td class="p-2 font-semibold border border-stone-200 align-top whitespace-nowrap">{{ $block['label'] }}</td>
                                            <td class="p-2 border border-stone-200 align-top">
                                                @if (count($block['exams'] ?? []) === 0)
                                                    <span class="text-es-muted">—</span>
                                                @else
                                                    <ul class="space-y-1">
                                                        @foreach ($block['exams'] as $exam)
                                                            <li class="flex justify-between gap-2">
                                                                <span>{{ $exam['title'] }} <span class="text-es-muted">({{ number_format($exam['weight'], 0) }}%)</span></span>
                                                                <span class="font-bold shrink-0">
                                                                    @if ($exam['score'] !== null)
                                                                        {{ number_format($exam['score'], 0) }}/100
                                                                    @else
                                                                        <span class="text-es-muted">—</span>
                                                                    @endif
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="p-2 border border-stone-200 text-center align-top font-black">
                                                @if (($block['average'] ?? null) !== null)
                                                    {{ number_format($block['average'], 1) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if (($skill['average'] ?? null) !== null)
                                    <tfoot>
                                        <tr class="bg-stone-50">
                                            <td colspan="2" class="p-2 border border-stone-200 font-bold text-right">Moyenne compétence</td>
                                            <td class="p-2 border border-stone-200 text-center font-black text-es-primary">{{ number_format($skill['average'], 1) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach
            </section>
        @empty
            <p class="text-es-muted text-center py-8">Aucune note enregistrée pour cette période.</p>
        @endforelse

        @if (! empty($report?->comments))
            <div class="mt-8 pt-6 border-t border-stone-200">
                <p class="text-xs font-bold uppercase text-es-muted mb-2">Appréciation du professeur</p>
                <p class="whitespace-pre-wrap text-es-ink">{{ $report->comments }}</p>
            </div>
        @endif

        <p class="text-xs text-es-muted mt-8 text-center">
            Document généré le {{ isset($report) ? $report->generated_at?->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}
            @if (isset($report) && $report->generatedBy)
                · {{ $report->generatedBy->name }}
            @endif
        </p>
    </div>
</div>
