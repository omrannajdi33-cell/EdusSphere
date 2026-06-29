@props(['theme'])

@if (! empty($theme['deco']))
    <div class="es-student-theme-deco" aria-hidden="true">
        @foreach ($theme['deco'] as $index => $symbol)
            <span
                class="es-student-theme-deco-item"
                style="--es-deco-i: {{ $index }};"
            >{{ $symbol }}</span>
        @endforeach
    </div>
@endif
