@props([
    'subject' => null,
    'slug' => null,
    'progress' => null,
    'href' => null,
])

@php
use App\Support\SubjectTheme;

$data = $subject ?? ($slug ? SubjectTheme::find($slug) : null);
@endphp

@if ($data)
    @php
        $tag = $href ? 'a' : 'div';
    @endphp
    <{{ $tag }}
        @if ($href) href="{{ $href }}" @endif
        {{ $attributes->merge(['class' => 'es-subject-card group']) }}
    >
        <div class="flex items-start justify-between gap-3">
            <x-subject-icon :icon="$data['icon']" :color="$data['color']"/>
            @if ($progress !== null)
                <span class="text-base font-black text-es-primary">{{ $progress }}%</span>
            @endif
        </div>
        <div>
            <p class="es-subject-name">{{ $data['name'] }}</p>
            @if ($slot->isNotEmpty())
                <p class="mt-1.5 text-base font-medium text-es-muted">{{ $slot }}</p>
            @endif
        </div>
        @if ($progress !== null)
            <x-progress-bar :value="$progress" :max="100" :color="$data['color']" class="mt-auto"/>
        @endif
    </{{ $tag }}>
@endif
