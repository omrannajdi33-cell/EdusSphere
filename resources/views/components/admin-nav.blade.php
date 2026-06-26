@props(['active' => null])

@php
$items = config('admin_nav', []);
$badges = [
    'pending_corrections' => \App\Models\Correction::whereIn('status', ['to_correct', 'submitted'])->count(),
    'unread_notifications' => auth()->check()
        ? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count()
        : 0,
];
@endphp

<nav
    {{ $attributes->merge(['class' => 'flex-1 px-3 py-2 space-y-1 overflow-y-auto']) }}
    aria-label="Navigation principale — espace professeur"
>
    <ul class="space-y-1" role="list">
        @foreach ($items as $item)
            @php
                $isActive = $active === $item['key'];
                if (! $isActive && ! empty($item['routes'])) {
                    foreach ($item['routes'] as $pattern) {
                        if (request()->routeIs($pattern)) {
                            $isActive = true;
                            break;
                        }
                    }
                }
            @endphp
            <li role="none">
                @if ($item['enabled'] && $item['route'])
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'es-nav-item',
                            'es-nav-item-active' => $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (! empty($item['badge']) && ($badges[$item['badge']] ?? 0) > 0)
                            <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-xs font-bold text-white" aria-label="{{ $badges[$item['badge']] }} en attente">
                                {{ $badges[$item['badge']] > 99 ? '99+' : $badges[$item['badge']] }}
                            </span>
                        @endif
                    </a>
                @else
                    <span class="es-nav-item-disabled" aria-disabled="true" title="Bientôt disponible">
                        <svg class="h-5 w-5 shrink-0 opacity-50" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                        <span class="sr-only">(bientôt)</span>
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
