@extends('layouts.admin')

@section('admin-content')
<div class="es-correction-page es-page-enter">
    <nav class="mb-5">
        <a href="{{ route('admin.projects.submissions', $project) }}" class="es-link text-sm font-bold">← Soumissions</a>
    </nav>

    <header class="es-correction-head mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-es-ink">{{ $student->full_name }}</h1>
            <p class="mt-1.5 text-base text-es-muted">
                <span class="font-semibold text-es-ink">{{ $project->title }}</span>
                · {{ $project->subject->name }}
            </p>
        </div>
        <span @class([
            'inline-flex shrink-0 items-center rounded-xl px-4 py-2 text-sm font-bold',
            'bg-emerald-100 text-emerald-800' => $submission->workflow_status === 'corrected',
            'bg-amber-100 text-amber-900' => $submission->workflow_status === 'submitted',
            'bg-stone-100 text-stone-700' => $submission->workflow_status === 'returned',
        ])>
            {{ $submission->statusLabel() }}
        </span>
    </header>

    <div class="es-correction-grid">
        <div class="space-y-4">
            @if (filled($submission->research_notes))
                <section class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Notes de recherche</h2>
                    <div class="whitespace-pre-wrap text-es-ink leading-relaxed">{{ $submission->research_notes }}</div>
                </section>
            @endif

            @if ($project->allowsOnlineWrite() && filled($submission->content))
                <section class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Travail rédigé</h2>
                    <div class="prose prose-sm max-w-none whitespace-pre-wrap text-es-ink leading-relaxed">{{ $submission->content }}</div>
                </section>
            @endif

            @if ($submission->files->isNotEmpty())
                <section class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Fichiers déposés</h2>
                    <ul class="space-y-2">
                        @foreach ($submission->files as $file)
                            <li>
                                <a href="{{ route('project-submission-files.show', [$project, $file]) }}" class="es-link font-bold" target="_blank">
                                    📎 {{ $file->displayName() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($project->require_sources && ! empty($submission->sources))
                <section class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Sources</h2>
                    <ul class="space-y-3 text-sm">
                        @foreach ($submission->sources as $source)
                            <li class="rounded-xl bg-stone-50 p-3 border border-stone-100">
                                <p class="font-bold">{{ $source['title'] ?? '' }}</p>
                                @if (! empty($source['author']))<p class="text-es-muted">{{ $source['author'] }}</p>@endif
                                @if (! empty($source['url']))<a href="{{ $source['url'] }}" class="es-link text-xs" target="_blank">{{ $source['url'] }}</a>@endif
                                @if (! empty($source['notes']))<p class="mt-1 text-es-muted">{{ $source['notes'] }}</p>@endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($project->require_bibliography && ! empty($submission->bibliography))
                <section class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-3">Bibliographie</h2>
                    <ul class="space-y-3 text-sm">
                        @foreach ($submission->bibliography as $entry)
                            <li class="rounded-xl bg-stone-50 p-3 border border-stone-100">
                                <p class="font-bold">{{ $entry['title'] ?? '' }}</p>
                                <p class="text-es-muted">
                                    {{ $entry['author'] ?? '' }}
                                    @if (! empty($entry['year'])) · {{ $entry['year'] }}@endif
                                    @if (! empty($entry['publisher'])) · {{ $entry['publisher'] }}@endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="es-correction-aside">
            <div class="es-card es-correction-panel p-5 space-y-4">
                <h2 class="font-extrabold text-lg">Validation</h2>

                @if ($correction?->status === 'validated')
                    @if ($correction->score !== null)
                        <p class="text-4xl font-black text-es-primary">{{ number_format($correction->score, 0) }}<span class="text-xl text-es-muted">/100</span></p>
                    @endif
                    @if ($correction->comment)
                        <p class="text-sm text-es-muted whitespace-pre-wrap rounded-xl bg-stone-50 p-4">{{ $correction->comment }}</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.projects.corrections.finalize', [$project, $student]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="score" class="es-label">Note /100</label>
                            <input type="number" name="score" id="score" min="0" max="100" step="0.5" required class="es-input w-full text-lg font-bold"
                                value="{{ old('score', $correction?->score) }}">
                        </div>
                        <div>
                            <label for="comment" class="es-label">Commentaire</label>
                            <textarea name="comment" id="comment" rows="4" class="es-textarea w-full">{{ old('comment', $correction?->comment) }}</textarea>
                        </div>
                        <x-button type="submit" class="w-full">Valider la correction</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.projects.corrections.return', [$project, $student]) }}" class="space-y-3 pt-4 border-t border-stone-200">
                        @csrf
                        <textarea name="comment" rows="3" required class="es-textarea w-full text-sm" placeholder="Explique ce qu'il faut corriger…"></textarea>
                        <x-button type="submit" variant="secondary" class="w-full">Renvoyer à l'élève</x-button>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
