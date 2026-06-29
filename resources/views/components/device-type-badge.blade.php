@props([
    'deviceType' => null,
    'size' => 'sm',
])

@php
    $meta = $deviceType ? config('edusphere.device_types.'.$deviceType) : null;
@endphp

@if ($meta)
    <span @class([
        'inline-flex items-center gap-1 rounded-lg font-bold',
        'px-2 py-0.5 text-[10px]' => $size === 'sm',
        'px-2.5 py-1 text-xs' => $size !== 'sm',
        'bg-sky-100 text-sky-900' => $deviceType === 'tablet',
        'bg-violet-100 text-violet-900' => $deviceType === 'computer',
    ])>
        <span aria-hidden="true">{{ $meta['icon'] ?? '' }}</span>
        {{ $meta['label'] ?? $deviceType }}
    </span>
@endif
