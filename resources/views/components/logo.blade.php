@props(['subtitle' => null, 'light' => false])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group']) }}>
    <span class="es-logo-mark">E</span>
    <span class="text-left">
        <span @class(['es-logo-name', '!text-white' => $light])>EduSphere</span>
        @if ($subtitle)
            <span @class(['es-logo-sub', '!text-sky-200' => $light])>{{ $subtitle }}</span>
        @endif
    </span>
</a>
