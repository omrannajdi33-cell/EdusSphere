@extends('layouts.student')

@section('student-content')
<div class="es-page-enter max-w-2xl mx-auto">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="es-page-title">Notifications</h1>
            <p class="es-page-subtitle">Corrections, renvois et messages</p>
        </div>
        @if ($notifications->whereNull('read_at')->count() > 0)
            <form method="POST" action="{{ route('student.notifications.read-all') }}">
                @csrf
                <x-button type="submit" variant="secondary" class="es-btn-sm">Tout marquer lu</x-button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $body = \App\Support\NotificationMessage::body($notification->type, $data);
                $isUnread = ! $notification->read_at;
            @endphp
            <article @class(['es-card p-5', 'ring-2 ring-es-primary/30' => $isUnread])>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-es-muted">
                            {{ \App\Support\NotificationMessage::label($notification->type) }}
                        </p>
                        <p class="font-extrabold mt-1">{{ $body }}</p>
                        @if (! empty($data['comment']))
                            <p class="text-sm text-es-muted mt-2 whitespace-pre-wrap">{{ $data['comment'] }}</p>
                        @endif
                        <p class="text-sm text-es-muted mt-2">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('student.notifications.read', $notification) }}">
                        @csrf
                        <x-button type="submit" variant="secondary" class="es-btn-sm">
                            {{ $isUnread ? 'Voir' : 'Ouvrir' }}
                        </x-button>
                    </form>
                </div>
            </article>
        @empty
            <div class="es-empty">
                <p class="font-extrabold">Aucune notification</p>
                <p class="text-es-muted mt-2">Tu seras prévenu quand le prof corrige ou renvoie un travail.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
