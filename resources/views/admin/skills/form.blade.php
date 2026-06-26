@extends('layouts.admin')

@php $isEdit = $skill->exists; @endphp

@section('admin-content')
<div class="es-page-enter max-w-lg">
    <div class="mb-8">
        <a href="{{ route('admin.subjects.skills.index', $subject) }}" class="es-link text-sm">← {{ $subject->name }}</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier la compétence' : 'Nouvelle compétence' }}</h1>
        <p class="text-sm font-medium text-es-muted mt-2">Total actuel (hors cette compétence) : {{ number_format($total, 0) }} %</p>
    </div>

    <x-card>
        <form method="POST" action="{{ $isEdit ? route('admin.subjects.skills.update', [$subject, $skill]) : route('admin.subjects.skills.store', $subject) }}" class="space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <x-input label="Nom de la compétence" name="name" value="{{ old('name', $skill->name) }}" required :error="$errors->first('name')"/>

            <x-input
                label="Pondération (%)"
                name="weight_percent"
                type="number"
                step="0.01"
                min="0.01"
                max="100"
                value="{{ old('weight_percent', $skill->weight_percent) }}"
                required
                :error="$errors->first('weight_percent')"
            />

            <p class="text-sm text-es-muted">Le total de toutes les compétences ne peut pas dépasser 100 %.</p>

            <div class="flex gap-3">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Ajouter' }}</x-button>
                <x-button href="{{ route('admin.subjects.skills.index', $subject) }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
