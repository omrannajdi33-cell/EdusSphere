@props([
    'name' => 'modal',
    'title' => '',
    'show' => false,
])

<div
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-5"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    <div class="es-modal-backdrop" @click="open = false"></div>
    <div
        {{ $attributes->merge(['class' => 'es-modal-panel']) }}
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        @if ($title)
            <h3 class="es-modal-title">{{ $title }}</h3>
        @endif
        {{ $slot }}
    </div>
</div>
