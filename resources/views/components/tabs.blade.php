@props([
    'tabs' => [],
    'default' => null,
])

@php
$defaultTab = $default ?? ($tabs[0]['id'] ?? '');
@endphp

<div x-data="{ tab: @js($defaultTab) }" {{ $attributes }}>
    @if (count($tabs))
        <div class="es-tab-bar mb-6" role="tablist">
            @foreach ($tabs as $t)
                <button
                    type="button"
                    role="tab"
                    @click="tab = @js($t['id'])"
                    :class="tab === @js($t['id']) ? 'es-tab es-tab-active' : 'es-tab'"
                >
                    {{ $t['label'] }}
                </button>
            @endforeach
        </div>
    @endif

    {{ $slot }}
</div>
