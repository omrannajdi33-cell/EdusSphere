@extends('layouts.admin')

@php $isEdit = $subject->exists; @endphp

@section('admin-content')
<div class="es-page-enter max-w-xl">
    <div class="mb-8">
        <a href="{{ route('admin.subjects.index') }}" class="es-link text-sm">← Retour aux matières</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier la matière' : 'Nouvelle matière' }}</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ $isEdit ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <x-input label="Nom" name="name" value="{{ old('name', $subject->name) }}" required :error="$errors->first('name')"/>

            <div>
                <label for="color" class="es-label">Couleur</label>
                <div class="flex gap-3 items-center">
                    <input id="color" type="color" name="color" value="{{ old('color', $subject->color) }}" class="h-12 w-16 rounded-xl border-0 cursor-pointer">
                    <input type="text" value="{{ old('color', $subject->color) }}" class="es-input flex-1 font-mono" readonly id="color-hex" aria-hidden="true">
                </div>
            </div>

            <div>
                <label for="icon" class="es-label">Icône</label>
                <select id="icon" name="icon" class="es-select">
                    @foreach ($icons as $key => $path)
                        <option value="{{ $key }}" @selected(old('icon', $subject->icon) === $key)>{{ $key }}</option>
                    @endforeach
                </select>
            </div>

            <x-input label="Ordre d'affichage" name="display_order" type="number" min="0" value="{{ old('display_order', $subject->display_order) }}"/>

            <div class="flex gap-3">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</x-button>
                <x-button href="{{ route('admin.subjects.index') }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
document.getElementById('color')?.addEventListener('input', (e) => {
    document.getElementById('color-hex').value = e.target.value;
});
</script>
@endpush
@endsection
