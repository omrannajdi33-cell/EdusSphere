@extends('layouts.admin')

@section('admin-content')
<x-card title="Dashboard Admin">
    <p class="text-slate-600">Bienvenue sur EduSphere. L'espace professeur sera développé en Phase 7.</p>
    <div class="mt-4">
        <x-button href="{{ url('/') }}" variant="secondary">Retour accueil</x-button>
    </div>
</x-card>
@endsection
