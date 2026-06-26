@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-8">
        <a href="{{ route('admin.activities.build', ['activity' => $activity, 'step' => 2]) }}" class="es-link text-sm">← Retour à l'éditeur</a>
        <h1 class="es-page-title mt-2">Copies soumises</h1>
        <p class="es-page-subtitle">{{ $activity->title }}</p>
    </div>

    <div class="space-y-3">
        @forelse ($submissions as $submission)
            @php $corr = $corrections->get($submission->student_id); @endphp
            <article class="es-card p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-extrabold text-lg">{{ $submission->student->full_name }}</p>
                    <p class="text-sm text-es-muted">
                        Soumis le {{ $submission->submitted_at?->format('d/m/Y H:i') }}
                        · {{ config('activity.workflow_statuses.'.$submission->workflow_status, $submission->workflow_status) }}
                    </p>
                    @if ($corr?->score !== null && $corr->status === 'validated')
                        <p class="text-sm font-bold text-es-primary mt-1">Note : {{ number_format($corr->score, 0) }}/100</p>
                    @endif
                </div>
                <x-button href="{{ route('admin.activities.corrections.show', [$activity, $submission->student]) }}" variant="secondary">
                    {{ $corr?->status === 'validated' ? 'Voir la correction' : 'Corriger à l\'encre' }}
                </x-button>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune copie soumise</p>
                <p class="text-es-muted mt-2">Les élèves doivent terminer et soumettre l'activité.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
