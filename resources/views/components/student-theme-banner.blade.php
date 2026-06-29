@props(['theme'])

@if (($theme['mode'] ?? 'default') !== 'default')
    <div
        class="es-student-theme-banner"
        role="status"
        aria-live="polite"
    >
        <div class="es-student-theme-banner-inner">
            @if (! empty($theme['emoji']))
                <span class="es-student-theme-banner-emoji" aria-hidden="true">{{ $theme['emoji'] }}</span>
            @endif

            <div class="min-w-0 flex-1">
                <p class="es-student-theme-banner-title">
                    {{ $theme['greeting'] ?? $theme['name'] }}
                    @if (! empty($theme['period_label']) && ($theme['mode'] ?? '') === 'subject')
                        <span class="es-student-theme-banner-period">· {{ $theme['period_label'] }}</span>
                    @endif
                </p>
                @if (! empty($theme['tagline']))
                    <p class="es-student-theme-banner-tagline">{{ $theme['tagline'] }}</p>
                @endif
            </div>

            @if (($theme['mode'] ?? '') === 'subject' && ! empty($theme['icon']))
                <x-subject-icon
                    :icon="$theme['icon']"
                    :color="$theme['color'] ?? '#4f46e5'"
                    size="sm"
                    class="shrink-0 shadow-md"
                />
            @endif
        </div>
    </div>
@endif
