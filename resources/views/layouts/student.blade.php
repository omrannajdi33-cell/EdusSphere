@extends('layouts.app')

@section('title', 'Mon espace — '.config('app.name'))

@section('content')
<div class="relative min-h-screen flex flex-col pb-32 lg:pb-10">
    <header class="es-header">
        <div class="es-header-inner">
            <x-logo subtitle="Mon espace" />
            <div class="flex items-center gap-2">
                @if (auth()->user()->student)
                    <a
                        href="{{ route('student.points.index') }}"
                        class="es-header-points-pill"
                        aria-label="Mes points : {{ ($pointsTotal ?? 0) >= 0 ? '+' : '' }}{{ $pointsTotal ?? 0 }}"
                    >
                        <span class="es-header-points-star" aria-hidden="true">⭐</span>
                        <span class="tabular-nums font-black">{{ ($pointsTotal ?? 0) >= 0 ? '+' : '' }}{{ $pointsTotal ?? 0 }}</span>
                    </a>
                @endif
                @php
                    $unreadCount = \App\Models\Notification::query()
                        ->where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                @endphp
                <a href="{{ route('student.notifications.index') }}"
                    class="relative rounded-full p-2 transition hover:bg-white/60 active:scale-95"
                    aria-label="Notifications{{ $unreadCount > 0 ? " ($unreadCount non lues)" : '' }}">
                    <svg class="w-6 h-6 text-es-ink" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if ($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-black flex items-center justify-center">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('student.profile') }}" class="rounded-full p-0.5 transition hover:scale-105 active:scale-95" aria-label="Mon profil">
                    <x-avatar
                        :name="auth()->user()->student?->full_name ?? auth()->user()->name"
                        :src="auth()->user()->student?->avatarUrl('student')"
                        size="sm"
                    />
                </a>
            </div>
        </div>
    </header>

    <x-student-bottom-nav :active="$activeNav ?? 'home'" />

    <main class="flex-1 es-container py-6 md:py-10">
        @yield('student-content')
    </main>
</div>
@endsection

@once('document-viewer-assets')
@push('scripts')
    @vite('resources/js/document-viewer.js')
@endpush
@endonce
