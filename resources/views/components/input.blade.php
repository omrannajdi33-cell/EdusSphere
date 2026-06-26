@props([
    'label' => null,
    'name',
    'type' => 'text',
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $name }}" class="es-label">{{ $label }}</label>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->except('class')->merge(['class' => 'es-input'.($error ? ' es-input-error' : '')]) }}
    />
    @if ($error)
        <p class="es-field-error">{{ $error }}</p>
    @endif
</div>
