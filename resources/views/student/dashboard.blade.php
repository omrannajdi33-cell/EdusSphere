@extends('layouts.student')

@section('student-content')
<x-card title="Mon espace">
    <p class="text-slate-600">Bienvenue élève ! Le dashboard sera développé en Phase 14.</p>
    <div class="mt-4">
        <x-button href="{{ url('/') }}" variant="secondary">Retour accueil</x-button>
    </div>
</x-card>
@endsection
