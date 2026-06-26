@extends('layouts.guest')

@section('title', 'Accueil — '.config('app.name'))

@section('content')
<div
    class="relative min-h-screen"
    x-data="{
        streak: {{ \Illuminate\Support\Js::from(1) }},
        reacted: null,
        message: '',
        init() {
            const today = @js(now()->format('Y-m-d'));
            const yesterday = @js(now()->subDay()->format('Y-m-d'));
            const key = 'edusphere_daily';
            const saved = JSON.parse(localStorage.getItem(key) || '{}');
            if (saved.lastVisit === today) {
                this.streak = saved.streak || 1;
                this.reacted = saved.reacted || null;
            } else if (saved.lastVisit === yesterday) {
                this.streak = (saved.streak || 0) + 1;
                localStorage.setItem(key, JSON.stringify({ lastVisit: today, streak: this.streak, reacted: null }));
            } else {
                this.streak = 1;
                localStorage.setItem(key, JSON.stringify({ lastVisit: today, streak: 1, reacted: null }));
            }
        },
        react(type) {
            this.reacted = type;
            this.message = type === 'wow'
                ? 'Super ! +1 curiosité 🚀 Reviens demain pour en apprendre plus.'
                : 'Expert en herbe ! 🎓 Demain, un nouveau défi t\'attend.';
            const key = 'edusphere_daily';
            const saved = JSON.parse(localStorage.getItem(key) || '{}');
            saved.reacted = type;
            localStorage.setItem(key, JSON.stringify(saved));
        }
    }"
>
    {{-- Header --}}
    <header class="es-header">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 md:px-8">
            <x-logo subtitle="Découverte quotidienne" />

            <div class="flex items-center gap-3">
                @auth
                    @if ($isStudent)
                        <a href="{{ route('student.dashboard') }}" class="hidden sm:inline-flex es-btn-secondary es-btn-sm">
                            Mon espace →
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="es-btn-secondary es-btn-sm !min-h-[2.75rem] !text-es-muted hover:!text-red-600 !border-transparent !shadow-none">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="es-btn-primary es-btn-sm">
                        Se connecter
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="relative z-10 mx-auto max-w-6xl px-5 py-8 md:px-8 md:py-12 es-page-enter">
        {{-- Greeting --}}
        <div class="mb-10 text-center md:text-left">
            @if ($firstName)
                <p class="text-base font-black uppercase tracking-[0.2em] text-es-primary">Bon retour</p>
                <h1 class="mt-3 es-page-title">Salut {{ $firstName }} ! 👋</h1>
            @else
                <p class="text-base font-black uppercase tracking-[0.2em] text-es-primary">Bienvenue explorateur</p>
                <h1 class="mt-3 es-page-title">Prêt pour la science ? 🔭</h1>
            @endif
            <p class="mt-4 text-xl font-semibold text-es-muted">{{ $discovery['date_label'] }}</p>
        </div>

        <div class="grid gap-7 lg:grid-cols-5 lg:gap-8">
            {{-- Découverte du jour --}}
            <section class="lg:col-span-3">
                <div class="es-discovery-card">
                    <div
                        class="es-discovery-glow -right-10 -top-10 h-44 w-44"
                        style="background: {{ $discovery['color'] }}"
                    ></div>

                    <div class="relative">
                        <div class="mb-7 flex flex-wrap items-center gap-3">
                            <span class="es-glass inline-flex items-center gap-2.5 rounded-full px-5 py-2.5 text-base font-black">
                                <span class="text-2xl">{{ $discovery['emoji'] }}</span>
                                {{ $discovery['category'] }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100/80 px-5 py-2.5 text-base font-black text-amber-700 border border-amber-200/60">
                                ✨ Découverte #{{ $discovery['day_of_year'] }}
                            </span>
                        </div>

                        <h2 class="text-3xl font-black leading-tight text-es-ink md:text-4xl">{{ $discovery['title'] }}</h2>

                        <p class="mt-7 text-xl leading-relaxed font-medium text-es-ink/85 md:text-2xl">
                            {{ $discovery['fact'] }}
                        </p>

                        <div class="mt-8 es-glass rounded-2xl p-6">
                            <p class="text-sm font-black uppercase tracking-widest text-es-primary">Question du jour</p>
                            <p class="mt-3 text-lg font-bold text-es-ink md:text-xl">{{ $discovery['question'] }}</p>
                        </div>

                        {{-- Engagement --}}
                        <div class="mt-8">
                            <p class="mb-5 text-base font-bold text-es-muted">As-tu appris quelque chose de nouveau ?</p>
                            <div class="flex flex-wrap gap-4">
                                <button
                                    type="button"
                                    @click="react('wow')"
                                    :class="reacted === 'wow' ? 'es-btn-primary scale-105' : 'es-btn-secondary'"
                                    class="es-btn !min-h-[3.25rem]"
                                >
                                    🤩 Wow !
                                </button>
                                <button
                                    type="button"
                                    @click="react('knew')"
                                    :class="reacted === 'knew' ? 'es-btn-primary scale-105' : 'es-btn-secondary'"
                                    class="es-btn !min-h-[3.25rem]"
                                >
                                    🧠 Je le savais !
                                </button>
                            </div>
                            <p x-show="reacted" x-cloak class="mt-5 text-base font-bold text-es-primary" x-text="message"></p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Sidebar --}}
            <aside class="flex flex-col gap-6 lg:col-span-2">
                {{-- Streak --}}
                <div class="es-streak-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-widest text-es-muted">Série de découvertes</p>
                            <p class="mt-2 text-5xl font-black text-amber-500" x-text="streak + ' jour' + (streak > 1 ? 's' : '')">0 jour</p>
                        </div>
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-amber-100/80 text-4xl border border-amber-200/50">🔥</div>
                    </div>
                    <p class="mt-5 text-base font-medium text-es-muted">Reviens demain pour une nouvelle info scientifique !</p>
                    <div class="mt-5 flex gap-1.5">
                        <template x-for="d in 7" :key="d">
                            <div
                                class="h-2.5 flex-1 rounded-full transition-all duration-500"
                                :class="d <= streak ? 'bg-gradient-to-r from-amber-400 to-orange-400 shadow-sm' : 'bg-violet-100'"
                            ></div>
                        </template>
                    </div>
                </div>

                {{-- Mission --}}
                <div class="es-mission-card">
                    <p class="text-sm font-black uppercase tracking-widest text-es-primary">Mission du jour</p>
                    <p class="mt-3 text-xl font-black text-es-ink">Connecte-toi et explore une activité !</p>
                    <p class="mt-3 text-base font-medium text-es-muted">Chaque jour compte pour ta progression et tes points ⭐</p>
                    @auth
                        @if ($isStudent)
                            <a href="{{ route('student.dashboard') }}" class="es-btn-primary mt-6 w-full">
                                Commencer ma journée →
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="es-btn-primary mt-6 w-full">
                            Me connecter →
                        </a>
                    @endauth
                </div>

                {{-- Quick links --}}
                <div class="grid grid-cols-2 gap-4">
                    @foreach ([['📚', 'Leçons'], ['✏️', 'Activités'], ['📝', 'Examens'], ['⭐', 'Points']] as [$icon, $label])
                        <div class="es-quick-link">
                            <div class="text-3xl">{{ $icon }}</div>
                            <div class="mt-2 text-sm font-black text-es-muted">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>

        @guest
            <p class="mt-12 text-center text-base font-medium text-es-muted">
                Comptes démo : <span class="font-bold text-es-ink">eleve1@edusphere.fr</span> · mot de passe <span class="font-bold text-es-ink">password</span>
            </p>
        @endguest
    </main>
</div>
@endsection
