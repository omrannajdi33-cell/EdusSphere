@extends('layouts.admin')

@php
    $isEdit = $student->exists;
@endphp

@section('admin-content')
<div class="es-page-enter max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.students.index') }}" class="es-link text-sm">← Retour aux élèves</a>
        <h1 class="es-page-title mt-4">{{ $isEdit ? 'Modifier l\'élève' : 'Nouvel élève' }}</h1>
    </div>

    <x-card>
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.students.update', $student) : route('admin.students.store') }}"
            enctype="multipart/form-data"
            class="space-y-5"
        >
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @if ($isEdit)
                <div class="flex items-center gap-4 pb-4 border-b border-stone-100">
                    <x-avatar
                        :name="$student->full_name"
                        :src="$student->avatar_path ? route('admin.students.avatar.show', $student) : null"
                        size="lg"
                    />
                    <div>
                        <p class="font-extrabold">{{ $student->full_name }}</p>
                        <label class="mt-2 flex items-center gap-2 text-sm font-medium text-es-muted">
                            <input type="checkbox" name="remove_avatar" value="1" class="es-checkbox">
                            Supprimer la photo
                        </label>
                    </div>
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-input label="Prénom" name="first_name" value="{{ old('first_name', $student->first_name) }}" required :error="$errors->first('first_name')"/>
                <x-input label="Nom" name="last_name" value="{{ old('last_name', $student->last_name) }}" required :error="$errors->first('last_name')"/>
            </div>

            <x-input label="Email" name="email" type="email" value="{{ old('email', $isEdit ? $user->email : '') }}" required :error="$errors->first('email')"/>

            <div>
                <label for="school_level_id" class="es-label">Niveau scolaire</label>
                <select id="school_level_id" name="school_level_id" class="es-select">
                    <option value="">— Choisir —</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(old('school_level_id', $student->school_level_id) == $level->id)>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <x-input label="Date de naissance" name="birth_date" type="date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}" :error="$errors->first('birth_date')"/>

            <div>
                <label for="status" class="es-label">Statut du compte</label>
                <select id="status" name="status" class="es-select">
                    <option value="active" @selected(old('status', $user->status) === 'active')>Actif</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')">Inactif</option>
                </select>
            </div>

            <div>
                <label for="avatar" class="es-label">Photo de profil (max 5 Mo)</label>
                <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="es-input !py-3">
                @error('avatar')<p class="es-field-error">{{ $message }}</p>@enderror
            </div>

            <x-input
                :label="$isEdit ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe'"
                name="password"
                type="password"
                :required="! $isEdit"
                placeholder="Minimum 3 caractères"
                :error="$errors->first('password')"
            />
            <x-input label="Confirmer le mot de passe" name="password_confirmation" type="password" :required="! $isEdit"/>

            <div class="flex gap-3 pt-2">
                <x-button type="submit">{{ $isEdit ? 'Enregistrer' : 'Créer l\'élève' }}</x-button>
                <x-button href="{{ route('admin.students.index') }}" variant="secondary">Annuler</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
