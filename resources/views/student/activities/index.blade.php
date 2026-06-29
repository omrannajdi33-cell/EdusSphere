@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes activités</h1>
        <p class="es-page-subtitle">Activités en classe publiées par ton professeur</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($activities as $activity)
            @include('student.activities.partials.card', [
                'activity' => $activity,
                'progress' => $progress->get($activity->id),
                'correction' => $corrections->get($activity->id),
            ])
        @empty
            <div class="sm:col-span-2 es-empty">
                <p class="font-extrabold">Aucune activité disponible</p>
                <p class="text-es-muted mt-2">Reviens plus tard !</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
