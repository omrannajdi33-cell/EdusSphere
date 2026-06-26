@extends('layouts.student')

@section('student-content')
<div class="es-page-enter">
    <div class="mb-8">
        <h1 class="es-page-title">Salut {{ $firstName }} ! 👋</h1>
        <p class="es-page-subtitle">Prêt à apprendre aujourd'hui ?</p>
    </div>

    {{-- Accès rapide --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-10">
        <a href="{{ route('student.schedule.index') }}" class="es-quick-link group">
            <span class="text-2xl block mb-1">📅</span>
            <span class="text-sm font-extrabold text-es-ink group-hover:text-es-primary">Mon horaire</span>
            @if ($todayCourses->isNotEmpty())
                <span class="text-xs font-bold text-es-primary mt-1 block">{{ $todayCourses->count() }} cours aujourd'hui</span>
            @else
                <span class="text-xs text-es-muted mt-1 block">Voir la semaine</span>
            @endif
        </a>
        <a href="{{ route('student.activities.index') }}" class="es-quick-link group">
            <span class="text-2xl block mb-1">✏️</span>
            <span class="text-sm font-extrabold text-es-ink group-hover:text-es-primary">Activités</span>
            <span class="text-xs font-bold text-es-primary mt-1 block">{{ $stats['activities_count'] }} disponible(s)</span>
        </a>
        <a href="{{ route('student.lessons.index') }}" class="es-quick-link group">
            <span class="text-2xl block mb-1">📚</span>
            <span class="text-sm font-extrabold text-es-ink group-hover:text-es-primary">Leçons</span>
            <span class="text-xs font-bold text-es-primary mt-1 block">{{ $stats['lessons_count'] }} disponible(s)</span>
        </a>
        <a href="{{ route('student.exams.index') }}" class="es-quick-link group">
            <span class="text-2xl block mb-1">📝</span>
            <span class="text-sm font-extrabold text-es-ink group-hover:text-es-primary">Examens</span>
            <span class="text-xs font-bold text-es-primary mt-1 block">{{ $stats['exams_active_count'] }} en cours</span>
        </a>
    </div>

    @if ($announcements->isNotEmpty())
        <div class="mb-10 space-y-3">
            <h2 class="es-section-title">Annonces</h2>
            @foreach ($announcements as $announcement)
                <x-alert type="info" :title="$announcement->title">
                    {{ $announcement->body }}
                    @if ($announcement->published_at)
                        <p class="text-xs mt-2 opacity-75">{{ $announcement->published_at->translatedFormat('j M Y H:i') }}</p>
                    @endif
                </x-alert>
            @endforeach
        </div>
    @endif

    @if ($notifications->isNotEmpty())
        <div class="mb-10 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <h2 class="es-section-title !mb-0">Notifications</h2>
                <a href="{{ route('student.notifications.index') }}" class="es-link text-sm font-bold">
                    Tout voir
                    @if ($unreadNotifications > 0)
                        ({{ $unreadNotifications }})
                    @endif
                    →
                </a>
            </div>
            @foreach ($notifications as $notification)
                @if ($notification->read_at)
                    @continue
                @endif
                @php
                    $data = $notification->data ?? [];
                    $body = \App\Support\NotificationMessage::body($notification->type, $data);
                @endphp
                <x-alert type="{{ $notification->type === 'activity_returned' ? 'warning' : 'info' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-bold">{{ $body }}</p>
                            @if (! empty($data['comment']))
                                <p class="text-sm mt-1 opacity-90">{{ $data['comment'] }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('student.notifications.read', $notification) }}">
                            @csrf
                            <x-button type="submit" variant="secondary" class="es-btn-sm">Ouvrir</x-button>
                        </form>
                    </div>
                </x-alert>
            @endforeach
        </div>
    @endif

    {{-- Horaire du jour --}}
    <div class="mb-10">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="es-section-title !mb-0">Aujourd'hui</h2>
            <a href="{{ route('student.schedule.index', ['view' => 'week']) }}" class="es-link text-sm font-bold">Horaire complet →</a>
        </div>

        @if ($todayCourses->isNotEmpty())
            <div class="space-y-2">
                @foreach ($todayCourses as $slot)
                    <div class="es-schedule-day-card" style="border-left-color: {{ $slot['color'] }}">
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold">{{ $slot['title'] }}</p>
                            <p class="text-sm text-es-muted">{{ $slot['subject'] ?? '' }} · {{ substr($slot['starts_at'], 0, 5) }}–{{ substr($slot['ends_at'], 0, 5) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-card class="!p-5">
                <p class="text-sm font-medium text-es-muted text-center">Pas de cours prévu aujourd'hui.</p>
                <p class="text-center mt-3">
                    <x-button href="{{ route('student.schedule.index') }}" variant="secondary" class="es-btn-sm">Voir mon horaire</x-button>
                </p>
            </x-card>
        @endif
    </div>

    {{-- Progression globale --}}
    <div class="es-hero mb-10">
        <p class="es-hero-label">Ma progression globale</p>
        <p class="es-hero-value">{{ $stats['global_progress'] }}%</p>
        <div class="es-hero-track">
            <div class="es-hero-bar" style="width: {{ $stats['global_progress'] }}%;"></div>
        </div>
        <p class="text-sm font-medium text-es-muted mt-3">
            {{ $stats['in_progress_count'] }} en cours · {{ $stats['to_complete_count'] }} à terminer
        </p>
    </div>

    {{-- Activités à faire --}}
    @if ($stats['featured_activities']->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="es-section-title !mb-0">Mes activités</h2>
                <a href="{{ route('student.activities.index') }}" class="es-link text-sm font-bold">Tout voir →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($stats['featured_activities'] as $item)
                    @php
                        $activity = $item['activity'];
                        $prog = $item['progression'];
                    @endphp
                    <article class="es-card p-5 flex flex-col">
                        <div class="flex items-center gap-3 mb-2">
                            <x-subject-icon :icon="$activity->subject->icon" :color="$activity->subject->color" size="sm"/>
                            <span class="text-sm font-bold text-es-muted">{{ $activity->subject->name }}</span>
                        </div>
                        <h3 class="font-extrabold text-es-ink">{{ $activity->title }}</h3>
                        @if ($prog && $prog->percent_complete > 0)
                            <x-progress-bar :value="$prog->percent_complete" :max="100" :color="$activity->subject->color" class="mt-3"/>
                        @endif
                        <x-button href="{{ route('student.activities.play', $activity) }}" class="mt-4 w-full es-btn-sm">
                            {{ $prog && $prog->percent_complete > 0 ? 'Continuer' : 'Commencer' }}
                        </x-button>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Matières avec vrais chiffres --}}
    <h2 class="es-section-title">Mes matières</h2>
    <div class="es-page-grid mb-12">
        @foreach ($stats['subjects'] as $row)
            <x-subject-card
                :subject="$row['subject']"
                :progress="$row['progress']"
                :href="route('student.activities.index')"
            >
                @if ($row['activities_count'] > 0 || $row['lessons_count'] > 0)
                    {{ $row['activities_count'] }} activité{{ $row['activities_count'] > 1 ? 's' : '' }}
                    @if ($row['lessons_count'] > 0)
                        · {{ $row['lessons_count'] }} leçon{{ $row['lessons_count'] > 1 ? 's' : '' }}
                    @endif
                @else
                    Rien pour l'instant
                @endif
            </x-subject-card>
        @endforeach
    </div>
</div>
@endsection
