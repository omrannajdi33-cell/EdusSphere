@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-10">
        <h1 class="es-page-title">Mon profil</h1>
        <p class="es-page-subtitle">Informations du compte professeur</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 max-w-4xl">
        <x-card title="Informations">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input label="Nom" name="name" value="{{ old('name', $user->name) }}" required :error="$errors->first('name')" />
                <x-input label="Email" name="email" type="email" value="{{ old('email', $user->email) }}" required :error="$errors->first('email')" />
                <x-button type="submit">Enregistrer</x-button>
            </form>
        </x-card>

        <x-card title="Mot de passe">
            <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input label="Mot de passe actuel" name="current_password" type="password" required />
                <x-input label="Nouveau mot de passe" name="password" type="password" required />
                <x-input label="Confirmation" name="password_confirmation" type="password" required />
                <x-button type="submit" variant="secondary">Changer le mot de passe</x-button>
            </form>
        </x-card>
    </div>
</div>
@endsection
