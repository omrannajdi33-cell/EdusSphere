@props([
    'tab' => '',
    'id' => null,
])

<div
    x-show="tab === @js($tab)"
    x-cloak
    role="tabpanel"
    @if ($id) id="{{ $id }}" @endif
    {{ $attributes->merge(['class' => 'es-page-enter']) }}
>
    {{ $slot }}
</div>
