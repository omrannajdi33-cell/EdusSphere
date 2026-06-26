@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Mon profil</h1>
        <p class="es-page-subtitle">Photo, informations et mot de passe</p>
    </div>

    <div class="space-y-6 max-w-2xl">
        <x-card>
            <div class="flex items-center gap-5 mb-8">
                <x-avatar :name="$student?->full_name ?? $user->name" size="lg" />
                <div>
                    <p class="text-xl font-extrabold text-es-ink">{{ $student?->full_name ?? $user->name }}</p>
                    <p class="text-base font-medium text-es-muted">{{ $student?->schoolLevel?->name ?? 'Niveau non défini' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('student.profile.avatar') }}" enctype="multipart/form-data" class="mb-4">
                @csrf
                <label class="es-label">Photo de profil (max 5 Mo)</label>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="es-input !py-3">
                @error('avatar')<p class="es-field-error">{{ $message }}</p>@enderror
                <x-button type="submit" variant="secondary" class="mt-4">Téléverser</x-button>
            </form>

            @if ($student?->avatar_path)
                <form method="POST" action="{{ route('student.profile.avatar.delete') }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" class="es-btn-sm">Supprimer la photo</x-button>
                </form>
            @endif
        </x-card>

        <x-card title="Mes informations">
            <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input label="Nom affiché" name="name" value="{{ old('name', $user->name) }}" required />
                <x-input label="Email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                <x-button type="submit">Enregistrer</x-button>
            </form>
        </x-card>

        <x-card title="Mot de passe">
            <form method="POST" action="{{ route('student.profile.password') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input label="Mot de passe actuel" name="current_password" type="password" required />
                <x-input label="Nouveau" name="password" type="password" required />
                <x-input label="Confirmation" name="password_confirmation" type="password" required />
                <x-button type="submit" variant="secondary">Modifier</x-button>
            </form>
        </x-card>
    </div>
</div>
@endsection
