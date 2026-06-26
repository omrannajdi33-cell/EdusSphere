@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter max-w-3xl">
    <a href="{{ route('admin.corrections.index') }}" class="es-link text-sm font-bold">← Corrections</a>

    <div class="mt-4 mb-6">
        <h1 class="es-page-title">{{ $exam->title }}</h1>
        <p class="es-page-subtitle">
            {{ $attempt->student->full_name }}
            · {{ $exam->subject->name ?? '' }}
            · Soumis le {{ $attempt->finished_at?->format('d/m/Y H:i') }}
        </p>
    </div>

    <div class="space-y-6 mb-8">
        @foreach ($exam->pages as $page)
            <section class="es-card p-5">
                <h2 class="font-extrabold text-lg mb-4">{{ $page->title }}</h2>
                <div class="space-y-4">
                    @foreach ($page->questions as $question)
                        @php
                            $answer = $answers->get($question->id);
                            $value = $answer?->content['value'] ?? null;
                            $review = \App\Support\QuestionGrader::evaluate($question, $value);
                        @endphp
                        <x-activity-question
                            :question="$question"
                            :value="$value"
                            :readonly="true"
                            :review="$review"
                        />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.exams.attempts.finalize', $attempt) }}" class="es-card p-5 space-y-4">
        @csrf
        <h2 class="font-extrabold text-lg">Validation</h2>
        @if ($suggestedScore ?? null)
            <p class="text-xs text-es-muted">Suggestion auto (questions interactives) : {{ number_format($suggestedScore, 1) }}/100</p>
        @endif
        <div>
            <label class="es-label">Note /100</label>
            <input type="number" name="score" min="0" max="100" step="0.5" class="es-input" required
                value="{{ old('score', $attempt->final_score ?? $suggestedScore ?? '') }}">
        </div>
        <div>
            <label class="es-label">Commentaire (optionnel)</label>
            <textarea name="comment" class="es-textarea" rows="3">{{ old('comment') }}</textarea>
        </div>
        <x-button type="submit">Valider la correction</x-button>
    </form>
</div>
@endsection
