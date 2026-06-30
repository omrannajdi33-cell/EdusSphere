@extends('layouts.admin')

@section('admin-content')
<div class="es-correction-page es-page-enter">
    <nav class="mb-5">
        <a href="{{ route('admin.corrections.index') }}" class="es-link text-sm font-bold inline-flex items-center gap-1.5">
            <span aria-hidden="true">←</span> Corrections
        </a>
    </nav>

    <header class="es-correction-head mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl md:text-3xl font-black text-es-ink tracking-tight">{{ $student->full_name }}</h1>
            <p class="mt-1.5 text-base text-es-muted">
                <span class="font-semibold text-es-ink">{{ $activity->title }}</span>
                <span class="mx-1.5">·</span>
                {{ $activity->subject->name }}
            </p>
        </div>
        @if ($correction?->status === 'validated')
            <span class="inline-flex shrink-0 items-center rounded-xl bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-800">
                ✓ Corrigée
                @if ($correction->score !== null)
                    · {{ number_format($correction->score, 0) }}/100
                @endif
            </span>
        @else
            <span class="inline-flex shrink-0 items-center rounded-xl bg-amber-100 px-4 py-2 text-sm font-bold text-amber-900">
                À corriger
            </span>
        @endif
    </header>

    <div class="es-correction-grid">
        <div class="es-correction-work">
            @include('student.activities.partials.player-shell', [
                'activity' => $activity,
                'progression' => null,
                'answers' => $answers,
                'previewMode' => false,
                'correctionMode' => true,
                'student' => $student,
                'correction' => $correction,
            ])
        </div>

        <aside class="es-correction-aside">
            @if ($activity->requiresResultPhoto() && $progression?->result_photo_path)
                <div class="es-card p-5 mb-4">
                    <h2 class="font-extrabold text-lg mb-3">📷 Photo du résultat</h2>
                    <a href="{{ route('activities.result-photo.show', [$activity, $student]) }}" target="_blank" class="block">
                        <img
                            src="{{ route('activities.result-photo.show', [$activity, $student]) }}"
                            alt="Photo du résultat de {{ $student->full_name }}"
                            class="w-full rounded-xl border border-stone-200 bg-white object-contain max-h-80"
                        >
                    </a>
                    <p class="text-xs text-es-muted mt-2">Clique sur l'image pour l'ouvrir en grand.</p>
                </div>
            @endif

            <div class="es-card es-correction-panel p-5 space-y-4">
                <h2 class="font-extrabold text-lg">Validation</h2>

                @if ($correction?->status === 'validated')
                    <p class="text-sm font-bold text-emerald-600">Correction déjà validée</p>
                    @if ($correction->score !== null)
                        <p class="text-4xl font-black text-es-primary tabular-nums">
                            {{ number_format($correction->score, 0) }}<span class="text-xl text-es-muted font-bold">/100</span>
                        </p>
                    @endif
                    @if ($correction->comment)
                        <p class="text-sm text-es-muted whitespace-pre-wrap rounded-xl bg-stone-50 p-4 border border-stone-100">{{ $correction->comment }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.activities.corrections.finalize', [$activity, $student]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="score" class="es-label">Note /100</label>
                            <input type="number" name="score" id="score" min="0" max="100" step="0.5" required
                                value="{{ old('score', $correction?->score ?? $suggestedScore ?? '') }}"
                                class="es-input w-full text-lg font-bold">
                            @if ($suggestedScore !== null)
                                <p class="text-xs text-es-muted mt-1.5">Suggestion auto (questions) : {{ number_format($suggestedScore, 1) }}/100</p>
                            @endif
                        </div>
                        <div>
                            <label for="comment" class="es-label">Commentaire (optionnel)</label>
                            <textarea name="comment" id="comment" rows="4" class="es-textarea w-full" placeholder="Bravo, continue comme ça…">{{ old('comment', $correction?->comment) }}</textarea>
                        </div>
                        <x-button type="submit" class="w-full">Valider la correction</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.activities.corrections.return', [$activity, $student]) }}" class="space-y-3 pt-4 border-t border-stone-200">
                        @csrf
                        <p class="text-sm font-bold text-es-ink">Renvoyer à l'élève</p>
                        <textarea name="comment" rows="3" required class="es-textarea w-full text-sm" placeholder="Explique ce qu'il faut corriger…">{{ old('return_comment') }}</textarea>
                        <x-button type="submit" variant="secondary" class="w-full">Renvoyer pour modification</x-button>
                    </form>
                @endif
            </div>

            @if ($correction && $correction->history->isNotEmpty())
                <div class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Historique</h2>
                    <ul class="space-y-3 text-sm max-h-64 overflow-y-auto pr-1">
                        @foreach ($correction->history->sortByDesc('created_at') as $entry)
                            <li class="border-l-2 border-stone-200 pl-3">
                                <p class="font-bold">{{ config('activity.correction_actions.'.$entry->action, $entry->action) }}</p>
                                <p class="text-es-muted text-xs mt-0.5">{{ $entry->created_at->format('d/m/Y H:i') }} · {{ $entry->user->name ?? '' }}</p>
                                @if ($entry->comment)
                                    <p class="mt-1 text-es-muted">{{ $entry->comment }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
