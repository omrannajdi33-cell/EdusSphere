@props([
    'type' => 'info',
    'title' => null,
])

@php
$class = match ($type) {
    'success' => 'es-alert es-alert-success',
    'warning' => 'es-alert es-alert-warning',
    'error' => 'es-alert es-alert-error',
    default => 'es-alert es-alert-info',
};
@endphp

<div {{ $attributes->merge(['class' => $class, 'role' => 'alert']) }}>
    @if ($title)
        <p class="font-extrabold mb-1">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
