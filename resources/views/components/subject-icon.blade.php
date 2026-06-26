@props([
    'icon' => 'book-open',
    'color' => '#0891b2',
    'size' => 'md',
])

@php
use App\Support\SubjectTheme;

$path = SubjectTheme::iconPath($icon);
$sizes = match ($size) {
    'sm' => 'h-9 w-9 rounded-xl [&_svg]:h-4 [&_svg]:w-4',
    'lg' => 'h-16 w-16 rounded-2xl [&_svg]:h-8 [&_svg]:w-8',
    default => 'h-[3.25rem] w-[3.25rem] rounded-2xl [&_svg]:h-6 [&_svg]:w-6',
};
@endphp

<span
    {{ $attributes->merge(['class' => "es-subject-icon inline-flex shrink-0 $sizes"]) }}
    style="background-color: {{ $color }};"
>
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
    </svg>
</span>
