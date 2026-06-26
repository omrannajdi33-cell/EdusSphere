@props(['title' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => ($padding ? 'es-card-padded' : 'es-card')]) }}>
    @if ($title)
        <h3 class="es-card-title">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
