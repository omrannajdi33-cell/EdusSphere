@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-10">
        <h1 class="es-page-title">Bonjour, {{ auth()->user()->name }} 👋</h1>
        <p class="es-page-subtitle">Tableau de bord professeur</p>
    </div>

    <div class="es-stat-grid mb-10">
        @foreach ([
            ['Élèves', $stats['students_count'], '#0891b2', route('admin.students.index')],
            ['Activités brouillon', $stats['pending_activities'], '#f97316', route('admin.activities.index', ['status' => 'draft'])],
            ['Projets brouillon', $stats['draft_projects'], '#8b5cf6', route('admin.projects.index', ['status' => 'draft'])],
            ['Examens actifs', $stats['active_exams'], '#2563eb', route('admin.exams.index')],
            ['Corrections en attente', $stats['pending_corrections'], '#ef4444', route('admin.corrections.index')],
        ] as [$label, $value, $color, $href])
            @if ($href)
                <a href="{{ $href }}" class="es-stat block hover:-translate-y-0.5 transition-transform">
            @else
                <div class="es-stat">
            @endif
                    <div class="es-stat-dot" style="background-color: {{ $color }};"></div>
                    <p class="es-stat-label">{{ $label }}</p>
                    <p class="es-stat-value">{{ $value }}</p>
            @if ($href)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Annonces publiées">
            <p class="text-4xl font-extrabold text-es-ink">{{ $stats['published_announcements'] }}</p>
            <p class="text-sm font-medium text-es-muted mt-2">Messages visibles par les élèves</p>
        </x-card>

        <x-card title="Dernières activités">
            @if ($stats['recent_activities']->isEmpty())
                <p class="text-es-muted font-medium">Aucune activité pour l'instant.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($stats['recent_activities'] as $activity)
                        <li class="flex items-center justify-between gap-3 rounded-2xl bg-stone-50 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-bold text-es-ink truncate">{{ $activity->title }}</p>
                                <p class="text-sm text-es-muted">{{ $activity->subject?->name }}</p>
                            </div>
                            <x-status-badge :status="$activity->status === 'published' ? 'published' : 'draft'"/>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Derniers projets">
            @if ($stats['recent_projects']->isEmpty())
                <p class="text-es-muted font-medium">Aucun projet pour l'instant.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($stats['recent_projects'] as $project)
                        <li class="flex items-center justify-between gap-3 rounded-2xl bg-stone-50 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-bold text-es-ink truncate">{{ $project->title }}</p>
                                <p class="text-sm text-es-muted">{{ $project->subject?->name }}</p>
                            </div>
                            <x-status-badge :status="$project->status === 'published' ? 'published' : 'draft'"/>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4">
                    <a href="{{ route('admin.projects.index') }}" class="es-link text-sm font-bold">Voir tous les projets →</a>
                </p>
            @endif
        </x-card>
    </div>
</div>
@endsection
