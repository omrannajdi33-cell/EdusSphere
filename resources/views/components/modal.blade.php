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
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div class="absolute inset-0 bg-slate-900/40" @click="open = false"></div>
    <div {{ $attributes->merge(['class' => 'relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl']) }}>
        @if ($title)
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $title }}</h3>
        @endif
        {{ $slot }}
    </div>
</div>
