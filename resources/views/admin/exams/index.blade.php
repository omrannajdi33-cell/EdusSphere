@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Examens</h1>
            <p class="es-page-subtitle">Chaque examen compte pour un % du bulletin</p>
        </div>
        <div class="flex gap-2">
            <x-button href="{{ route('admin.reports.index') }}" variant="secondary">Voir le bulletin</x-button>
            <x-button href="{{ route('admin.exams.create') }}">+ Nouvel examen</x-button>
        </div>
    </div>

    <x-card class="mb-6 !p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3" aria-label="Filtrer les examens">
            <select name="device" class="es-select sm:w-48">
                <option value="">Tous matériels</option>
                @foreach (config('edusphere.device_types') as $key => $meta)
                    <option value="{{ $key }}" @selected(($deviceFilter ?? null) === $key)>{{ $meta['icon'] ?? '' }} {{ $meta['label'] }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrer</x-button>
        </form>
    </x-card>

    <div class="space-y-4">
        @forelse ($exams as $exam)
            <div class="es-card p-5">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <p class="font-extrabold text-lg text-es-ink">{{ $exam->title }}</p>
                            <x-status-badge :status="match($exam->status) { 'open' => 'published', 'closed' => 'draft', default => 'draft' }" :label="config('exam.statuses.'.$exam->status)"/>
                            <x-device-type-badge :device-type="$exam->device_type"/>
                        </div>
                        <p class="text-sm text-es-muted">{{ $exam->subject->name }} · {{ $exam->skill->name }}</p>
                        <p class="text-sm text-es-muted mt-1">
                            {{ $exam->opens_at->translatedFormat('j M Y H:i') }} → {{ $exam->closes_at->translatedFormat('j M Y H:i') }}
                            · {{ $exam->duration_minutes }} min
                        </p>
                        <p class="text-sm font-bold text-es-primary mt-2">
                            Poids bulletin : {{ number_format($exam->weight_percent, 0) }}%
                            @if ($exam->reportPeriod) · {{ $exam->reportPeriod->label }} @endif
                        </p>
                        <p class="text-xs text-es-muted mt-1">{{ $exam->pages_count }} étape(s) de contenu</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('admin.exams.build', $exam) }}" variant="secondary" class="es-btn-sm">Éditer</x-button>
                        @if ($exam->status !== 'open')
                            <form method="POST" action="{{ route('admin.exams.open', $exam) }}">@csrf<x-button type="submit" class="es-btn-sm">Ouvrir</x-button></form>
                        @endif
                        @if ($exam->status !== 'closed')
                            <form method="POST" action="{{ route('admin.exams.close', $exam) }}">@csrf<x-button type="submit" variant="secondary" class="es-btn-sm">Fermer</x-button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Supprimer cet examen et toutes les tentatives associées ?')">
                            @csrf @method('DELETE')
                            <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucun examen</p>
                <p class="text-es-muted mt-2">Crée un examen avec son contenu et son poids dans le bulletin.</p>
            </div>
        @endforelse
    </div>

    @if ($exams->hasPages())
        <div class="mt-8">{{ $exams->links() }}</div>
    @endif
</div>
@endsection
