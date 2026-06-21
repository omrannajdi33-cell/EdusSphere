@extends('layouts.app')

@section('title', 'Admin — ' . config('app.name'))

@section('content')
<div class="min-h-screen flex">
    <aside class="hidden md:flex w-64 flex-col bg-white border-r border-slate-200 p-6">
        <div class="text-xl font-bold text-indigo-600 mb-8">EduSphere</div>
        <nav class="space-y-2 text-sm">
            <a href="{{ url('/admin') }}" class="block rounded-xl px-3 py-2 hover:bg-indigo-50">Dashboard</a>
            <span class="block rounded-xl px-3 py-2 text-slate-400">Élèves (bientôt)</span>
            <span class="block rounded-xl px-3 py-2 text-slate-400">Matières (bientôt)</span>
        </nav>
    </aside>
    <main class="flex-1 p-6">
        @yield('admin-content')
    </main>
</div>
@endsection
