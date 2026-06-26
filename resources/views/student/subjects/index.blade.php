@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes matières</h1>
        <p class="es-page-subtitle">8 matières officielles avec leurs compétences</p>
    </div>

    <div class="space-y-6">
        @foreach ($subjects as $subject)
            <div class="es-card p-6">
                <div class="flex items-start gap-4 mb-5">
                    <x-subject-icon :icon="$subject->icon" :color="$subject->color"/>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-extrabold text-es-ink">{{ $subject->name }}</h2>
                        <p class="text-sm font-medium text-es-muted mt-1">{{ $subject->skills->count() }} compétences · 100 % pondéré</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($subject->skills as $skill)
                        <div class="flex items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-sm font-bold text-es-ink truncate">{{ $skill->name }}</span>
                                    <span class="text-sm font-extrabold text-es-primary shrink-0">{{ number_format($skill->weight_percent, 0) }} %</span>
                                </div>
                                <div class="es-progress-track">
                                    <div class="es-progress-fill" style="width: {{ $skill->weight_percent }}%; background: {{ $subject->color }};"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
