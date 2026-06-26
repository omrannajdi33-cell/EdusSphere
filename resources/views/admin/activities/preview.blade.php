@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-6">
        <a href="{{ route('admin.activities.editor', $activity) }}" class="es-link text-sm">← Éditeur</a>
        <h1 class="es-page-title mt-2">Aperçu professeur</h1>
        <p class="es-page-subtitle">{{ $activity->title }}</p>
    </div>

    @include('student.activities.partials.player-shell', [
        'activity' => $activity,
        'progression' => null,
        'answers' => collect(),
        'previewMode' => true,
    ])
</div>
@endsection
