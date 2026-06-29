@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="es-page-title">Notifications</h1>
            <p class="es-page-subtitle">Soumissions, messages et alertes</p>
        </div>
        @if ($notifications->whereNull('read_at')->count() > 0)
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <x-button type="submit" variant="secondary">Tout marquer comme lu</x-button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $label = \App\Support\NotificationMessage::body($notification->type, $data);
            @endphp
            <article @class(['es-card p-5', 'ring-2 ring-es-primary/30' => ! $notification->read_at])>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-extrabold">{{ $label }}</p>
                        <p class="text-sm text-es-muted mt-1">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                        @csrf
                        @if (! empty($data['url']))
                            <input type="hidden" name="redirect" value="{{ $data['url'] }}">
                        @endif
                        <x-button type="submit" variant="secondary" class="es-btn-sm">
                            {{ $notification->read_at ? 'Voir' : 'Marquer lu' }}
                        </x-button>
                    </form>
                </div>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune notification</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
