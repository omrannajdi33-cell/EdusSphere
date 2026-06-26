@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Annonces</h1>
            <p class="es-page-subtitle">{{ $announcements->total() }} annonce(s)</p>
        </div>
        <x-button href="{{ route('admin.announcements.create') }}">+ Nouvelle annonce</x-button>
    </div>

    <div class="space-y-4">
        @forelse ($announcements as $announcement)
            <div class="es-card p-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <p class="font-extrabold text-lg text-es-ink">{{ $announcement->title }}</p>
                            <x-status-badge :status="$announcement->published_at ? 'published' : 'draft'" :label="$announcement->published_at ? 'Publiée' : 'Brouillon'"/>
                        </div>
                        <p class="text-sm text-es-muted line-clamp-2">{{ $announcement->body }}</p>
                        <p class="text-xs font-bold text-es-primary mt-2">
                            @if ($announcement->target_type === 'all') Tous les élèves
                            @elseif ($announcement->target_type === 'level') Niveau #{{ $announcement->target_id }}
                            @else Élève #{{ $announcement->target_id }}
                            @endif
                            @if ($announcement->published_at)
                                · {{ $announcement->published_at->translatedFormat('j M Y H:i') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('admin.announcements.edit', $announcement) }}" variant="secondary" class="es-btn-sm">Modifier</x-button>
                        @if (! $announcement->published_at)
                            <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">@csrf<x-button type="submit" class="es-btn-sm">Publier</x-button></form>
                        @else
                            <form method="POST" action="{{ route('admin.announcements.unpublish', $announcement) }}">@csrf<x-button type="submit" variant="secondary" class="es-btn-sm">Dépublier</x-button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune annonce</p>
            </div>
        @endforelse
    </div>

    @if ($announcements->hasPages())
        <div class="mt-8">{{ $announcements->links() }}</div>
    @endif
</div>
@endsection
