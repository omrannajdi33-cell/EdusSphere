@extends('layouts.student')

@section('student-content')
<div class="es-page-enter max-w-4xl mx-auto">
    <a href="{{ route('student.lessons.index') }}" class="es-link text-sm font-bold">← Mes leçons</a>

    <div class="mt-4 mb-8">
        <div class="flex items-start gap-4">
            <x-subject-icon :icon="$lesson->subject->icon" :color="$lesson->subject->color" size="lg"/>
            <div>
                <h1 class="es-page-title">{{ $lesson->title }}</h1>
                <p class="es-page-subtitle">{{ $lesson->subject->name }} · {{ $lesson->skill->name }}@if ($lesson->category) · {{ $lesson->category }}@endif</p>
            </div>
        </div>
    </div>

    @if ($lesson->description)
        <x-card class="mb-6 !p-0 overflow-hidden">
            <x-lesson-content :content="$lesson->description" class="p-6"/>
        </x-card>
    @endif

    @if ($lesson->hasExternalLinks())
        <x-card class="mb-6">
            <h2 class="text-lg font-extrabold text-es-ink mb-1">Liens à consulter</h2>
            <p class="text-sm text-es-muted mb-4">Ressources optionnelles proposées par ton professeur.</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($lesson->externalLinksForDisplay() as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="es-lesson-external-link">
                        <span aria-hidden="true">🔗</span>
                        <span>{{ $link['label'] !== '' ? $link['label'] : 'Ouvrir le lien' }}</span>
                        <span class="text-xs opacity-70" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif

    @forelse ($lesson->mediaFiles as $media)
        @php
            $ann = $annotations->get($media->id);
            $annPages = $ann?->content['pages'] ?? [];
            $kind = strtoupper($media->source_kind ?? 'PDF');
        @endphp
        <x-card class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <p class="font-extrabold text-lg">{{ $media->displayName() }}</p>
                    <p class="text-xs font-bold text-es-muted mt-1">{{ $kind }} · Lecteur EduSphere</p>
                </div>
                @if ($media->page_count)
                    <span class="text-sm font-semibold text-es-muted">{{ $media->page_count }} page(s)</span>
                @endif
            </div>
            <x-document-viewer
                :file-url="route('lesson-media.show', [$lesson, $media])"
                :doc-kind="$media->source_kind ?? 'pdf'"
                :save-url="route('student.lessons.annotations.save', $lesson)"
                :read-only="false"
                :initial-annotations="$annPages"
                :media-file-id="$media->id"
            />
        </x-card>
    @empty
        <x-alert type="info" title="Pas encore de document">
            Le professeur n'a pas encore ajouté de PDF ou PowerPoint à cette leçon.
        </x-alert>
    @endforelse
</div>
@endsection
