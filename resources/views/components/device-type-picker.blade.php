@props([
    'name' => 'device_type',
    'value' => null,
    'required' => true,
    'error' => null,
])

@php
    $selected = old($name, $value);
    $types = config('edusphere.device_types', []);
@endphp

<fieldset>
    <legend class="es-label mb-3">Matériel requis</legend>
    <p class="text-xs text-es-muted mb-3 -mt-1">Pour organiser la répartition des tablettes et ordinateurs en classe.</p>
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($types as $typeKey => $typeMeta)
            <label @class([
                'es-homework-slot-option',
                'es-homework-slot-option-active' => (string) $selected === (string) $typeKey,
            ])>
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $typeKey }}"
                    class="sr-only"
                    @checked((string) $selected === (string) $typeKey)
                    @if ($required) required @endif
                >
                <span class="text-2xl mb-2 block" aria-hidden="true">{{ $typeMeta['icon'] ?? '📦' }}</span>
                <span class="font-extrabold text-es-ink">{{ $typeMeta['label'] ?? $typeKey }}</span>
                @if (! empty($typeMeta['hint']))
                    <span class="text-xs text-es-muted mt-1 block">{{ $typeMeta['hint'] }}</span>
                @endif
            </label>
        @endforeach
    </div>
    @if ($error)
        <p class="es-field-error mt-2">{{ $error }}</p>
    @endif
</fieldset>
