@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-8">
        <a href="{{ route('admin.subjects.index') }}" class="es-link text-sm">← Matières</a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-4">
            <div class="flex items-center gap-4">
                <x-subject-icon :icon="$subject->icon" :color="$subject->color"/>
                <div>
                    <h1 class="es-page-title">{{ $subject->name }}</h1>
                    <p class="es-page-subtitle">Compétences & pondération</p>
                </div>
            </div>
            <x-button href="{{ route('admin.subjects.skills.create', $subject) }}">+ Compétence</x-button>
        </div>
    </div>

    <div @class([
        'es-card p-5 mb-6 border-2',
        'border-emerald-300 bg-emerald-50' => $isValidTotal,
        'border-amber-300 bg-amber-50' => ! $isValidTotal,
    ])>
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-es-muted">Total pondéré</p>
                <p class="text-3xl font-extrabold text-es-ink">{{ number_format($total, 0) }} %</p>
            </div>
            @if ($isValidTotal)
                <span class="es-badge es-badge-published">100 % validé</span>
            @else
                <span class="es-badge es-badge-progress">Doit totaliser 100 %</span>
            @endif
        </div>
        <div class="es-progress-track mt-4">
            <div class="es-progress-fill" style="width: {{ min(100, $total) }}%; background: {{ $subject->color }};"></div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($skills as $skill)
            <div class="es-card p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-es-ink">{{ $skill->name }}</p>
                    <div class="es-progress-track mt-2 max-w-xs">
                        <div class="es-progress-fill" style="width: {{ $skill->weight_percent }}%; background: {{ $subject->color }};"></div>
                    </div>
                </div>
                <p class="text-2xl font-extrabold text-es-primary shrink-0">{{ number_format($skill->weight_percent, 0) }} %</p>
                <div class="flex gap-2 shrink-0">
                    <x-button href="{{ route('admin.subjects.skills.edit', [$subject, $skill]) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                    <form method="POST" action="{{ route('admin.subjects.skills.destroy', [$subject, $skill]) }}" onsubmit="return confirm('Supprimer cette compétence ?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                    </form>
                </div>
            </div>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune compétence</p>
                <p class="text-es-muted mt-2">Ajoute des compétences jusqu'à 100 %.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
