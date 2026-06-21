@extends('layouts.app')

@section('title', 'Élève — ' . config('app.name'))

@section('content')
<div class="min-h-screen pb-24">
    <main class="p-4 md:p-6 max-w-5xl mx-auto">
        @yield('student-content')
    </main>
    <x-student-bottom-nav :active="$activeNav ?? 'home'" />
</div>
@endsection
