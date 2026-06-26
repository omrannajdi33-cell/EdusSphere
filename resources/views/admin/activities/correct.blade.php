@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-6">
        <a href="{{ route('admin.corrections.index') }}" class="es-link text-sm">← Corrections</a>
        <h1 class="es-page-title mt-2">Correction — {{ $student->full_name }}</h1>
        <p class="es-page-subtitle">{{ $activity->title }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div>
            @include('student.activities.partials.player-shell', [
                'activity' => $activity,
                'progression' => null,
                'answers' => $answers,
                'previewMode' => false,
                'correctionMode' => true,
                'student' => $student,
            ])
        </div>

        <aside class="space-y-4">
            <div class="es-card p-5 space-y-4">
                <h2 class="font-extrabold text-lg">Validation</h2>

                @if ($correction?->status === 'validated')
                    <p class="text-sm font-bold text-emerald-600">Correction déjà validée</p>
                    @if ($correction->score !== null)
                        <p class="text-3xl font-black text-es-primary">{{ number_format($correction->score, 0) }}<span class="text-lg">/100</span></p>
                    @endif
                    @if ($correction->comment)
                        <p class="text-sm text-es-muted whitespace-pre-wrap">{{ $correction->comment }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.activities.corrections.finalize', [$activity, $student]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="score" class="block text-sm font-bold mb-1">Note /100</label>
                            <input type="number" name="score" id="score" min="0" max="100" step="0.5" required
                                value="{{ old('score', $correction?->score ?? $suggestedScore ?? '') }}"
                                class="es-input w-full">
                            @if ($suggestedScore !== null)
                                <p class="text-xs text-es-muted mt-1">Suggestion auto (questions interactives) : {{ number_format($suggestedScore, 1) }}/100</p>
                            @endif
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-bold mb-1">Commentaire (optionnel)</label>
                            <textarea name="comment" id="comment" rows="4" class="es-textarea w-full" placeholder="Bravo, continue comme ça…">{{ old('comment', $correction?->comment) }}</textarea>
                        </div>
                        <x-button type="submit" class="w-full">Valider la correction</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.activities.corrections.return', [$activity, $student]) }}" class="space-y-3 pt-4 border-t border-stone-200">
                        @csrf
                        <p class="text-sm font-bold text-es-muted">Renvoyer à l'élève pour modification</p>
                        <textarea name="comment" rows="3" required class="es-textarea w-full" placeholder="Explique ce qu'il faut corriger…">{{ old('return_comment') }}</textarea>
                        <x-button type="submit" variant="secondary" class="w-full">Renvoyer à l'élève</x-button>
                    </form>
                @endif
            </div>

            @if ($correction && $correction->history->isNotEmpty())
                <div class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Historique</h2>
                    <ul class="space-y-3 text-sm">
                        @foreach ($correction->history->sortByDesc('created_at') as $entry)
                            <li class="border-l-2 border-stone-200 pl-3">
                                <p class="font-bold">{{ config('activity.correction_actions.'.$entry->action, $entry->action) }}</p>
                                <p class="text-es-muted">{{ $entry->created_at->format('d/m/Y H:i') }} · {{ $entry->user->name ?? '' }}</p>
                                @if ($entry->comment)
                                    <p class="mt-1">{{ $entry->comment }}</p>
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
