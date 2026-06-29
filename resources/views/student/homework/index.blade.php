@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes devoirs</h1>
        <p class="es-page-subtitle">
            @if ($pendingCount > 0)
                {{ $pendingCount }} devoir{{ $pendingCount > 1 ? 's' : '' }} à faire
            @else
                Tous tes devoirs sont à jour — bravo !
            @endif
        </p>
    </div>

    <section class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl" aria-hidden="true">🏫</span>
            <div>
                <h2 class="text-xl font-black text-es-ink">Pendant l'école</h2>
                <p class="text-sm text-es-muted">Devoirs à faire en classe ou pendant les heures scolaires.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @forelse ($duringSchool as $activity)
                @include('student.activities.partials.card', [
                    'activity' => $activity,
                    'progress' => $progress->get($activity->id),
                    'correction' => $corrections->get($activity->id),
                    'showHomeworkMeta' => true,
                ])
            @empty
                <div class="sm:col-span-2 es-empty es-homework-empty">
                    <p class="font-extrabold">Aucun devoir pendant l'école</p>
                    <p class="text-es-muted mt-2">Rien à faire pour l'instant.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl" aria-hidden="true">🏠</span>
            <div>
                <h2 class="text-xl font-black text-es-ink">Après l'école</h2>
                <p class="text-sm text-es-muted">Devoirs à faire à la maison, après les cours.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @forelse ($afterSchool as $activity)
                @include('student.activities.partials.card', [
                    'activity' => $activity,
                    'progress' => $progress->get($activity->id),
                    'correction' => $corrections->get($activity->id),
                    'showHomeworkMeta' => true,
                ])
            @empty
                <div class="sm:col-span-2 es-empty es-homework-empty">
                    <p class="font-extrabold">Aucun devoir après l'école</p>
                    <p class="text-es-muted mt-2">Profite de ton temps libre !</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
