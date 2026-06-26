@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes résultats</h1>
        <p class="es-page-subtitle">Notes et moyennes de tes activités</p>
    </div>

    <div class="es-points-hero mb-8">
        <p class="text-base font-extrabold text-es-muted uppercase tracking-wide">Moyenne générale</p>
        <p class="es-points-value">{{ $general !== null ? number_format($general, 0) : '—' }}</p>
        <p class="text-sm font-bold text-es-muted mt-2">sur 100</p>
    </div>

    @if ($bySubject !== [])
        <x-card title="Moyennes par matière" class="mb-8">
            <x-chart :items="collect($bySubject)->map(fn ($value, $subjectId) => [
                'label' => $subjects->get($subjectId)?->name ?? 'Matière',
                'value' => (float) $value,
                'color' => $subjects->get($subjectId)?->color ?? '#6366f1',
            ])->values()->all()"/>
        </x-card>
    @endif

    <x-card title="Notes par activité">
        @if ($activityGrades->isEmpty())
            <p class="text-es-muted">Aucune note pour le moment. Termine et soumets des activités !</p>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($activityGrades as $grade)
                    <li class="py-4 flex items-center justify-between gap-4">
                        <span class="font-bold">{{ $activityTitles->get($grade->source_id) ?? 'Activité #'.$grade->source_id }}</span>
                        <span class="text-xl font-black text-es-primary">{{ number_format($grade->value, 0) }}/100</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
</div>
@endsection
