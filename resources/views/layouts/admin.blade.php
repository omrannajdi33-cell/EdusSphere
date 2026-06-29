@extends('layouts.app')

@section('title', 'Admin — '.config('app.name'))

@section('content')
<a href="#admin-main" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:font-bold focus:shadow-es">
    Aller au contenu principal
</a>

<div class="relative min-h-screen flex" x-data="{ mobileNav: false }">
    <aside class="es-sidebar" aria-label="Barre latérale professeur">
        <div class="es-sidebar-head">
            <x-logo subtitle="Espace professeur" />
        </div>
        <x-admin-nav :active="$adminNav ?? null" class="flex-1 px-3 py-2"/>
        <div class="es-sidebar-foot">
            <div class="es-glass rounded-2xl p-5">
                <p class="text-lg font-black text-es-ink truncate">{{ auth()->user()->name }}</p>
                <p class="text-base font-medium text-es-muted truncate mt-1">{{ auth()->user()->email }}</p>
                <div class="mt-5 flex gap-5">
                    <a href="{{ route('admin.settings') }}" class="es-link text-base">Profil</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-base font-bold text-es-muted hover:text-red-600 transition-colors">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="es-header lg:hidden">
            <div class="flex items-center justify-between px-6 py-5 gap-4">
                <x-logo subtitle="Professeur" />
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="es-btn es-btn-secondary es-btn-sm"
                        @click="mobileNav = !mobileNav"
                        :aria-expanded="mobileNav.toString()"
                        aria-controls="mobile-admin-nav"
                    >
                        <span class="sr-only">Menu</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </button>
                    <a href="{{ route('admin.settings') }}" class="es-link text-base">Profil</a>
                </div>
            </div>
            <div
                id="mobile-admin-nav"
                x-show="mobileNav"
                x-cloak
                @click.outside="mobileNav = false"
                class="border-t border-stone-200 bg-white/95 backdrop-blur-lg px-3 py-3 max-h-[70vh] overflow-y-auto"
            >
                <x-admin-nav :active="$adminNav ?? null"/>
            </div>
        </header>
        <main id="admin-main" @class([
            'flex-1 p-6 md:p-10 w-full',
            'max-w-none' => ($adminNav ?? null) === 'schedules',
            'max-w-6xl mx-auto' => ($adminNav ?? null) !== 'schedules',
        ]) tabindex="-1">
            @yield('admin-content')
        </main>
    </div>
</div>
@endsection
