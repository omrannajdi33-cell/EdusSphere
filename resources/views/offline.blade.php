@extends('layouts.app')

@section('title', 'Hors ligne — EduSphere')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <x-card title="Pas de connexion">
        <p class="text-slate-600">Vérifie ta connexion internet et réessaie.</p>
    </x-card>
</div>
@endsection
