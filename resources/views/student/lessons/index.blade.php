@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mes leçons</h1>
        <p class="es-page-subtitle">{{ $lessons->count() }} leçon(s) disponible(s)</p>
    </div>

    @if ($lessons->isEmpty())
        <x-card>
            <p class="text-base font-medium text-es-muted text-center py-10">Tes leçons apparaîtront ici dès qu'elles seront publiées.</p>
        </x-card>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($lessons as $lesson)
                <a href="{{ route('student.lessons.show', $lesson) }}" class="es-card p-5 block hover:-translate-y-0.5 transition-transform">
                    <div class="flex items-start gap-3">
                        <x-subject-icon :icon="$lesson->subject->icon" :color="$lesson->subject->color"/>
                        <div class="min-w-0">
                            <p class="font-extrabold text-lg text-es-ink">{{ $lesson->title }}</p>
                            <p class="text-sm text-es-muted">{{ $lesson->subject->name }}</p>
                            @if ($lesson->estimated_duration_min)
                                <p class="text-xs font-bold text-es-primary mt-2">~ {{ $lesson->estimated_duration_min }} min</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
