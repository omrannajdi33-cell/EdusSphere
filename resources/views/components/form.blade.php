@props([
    'action',
    'method' => 'POST',
    'spacing' => 'normal',
])

@php
$spaceClass = match ($spacing) {
    'tight' => 'space-y-4',
    'loose' => 'space-y-8',
    default => 'space-y-5',
};
@endphp

<form
    action="{{ $action }}"
    method="{{ in_array(strtoupper($method), ['GET', 'POST']) ? $method : 'POST' }}"
    {{ $attributes->merge(['class' => $spaceClass]) }}
>
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif
    {{ $slot }}
</form>
