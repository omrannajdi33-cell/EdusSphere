@props(['active' => null])

@php
$navIcons = config('subjects.icons');

$items = [
    ['label' => 'Accueil', 'href' => route('student.dashboard'), 'key' => 'home', 'icon' => $navIcons['home']],
    ['label' => 'Matières', 'href' => route('student.subjects.index'), 'key' => 'subjects', 'icon' => $navIcons['book-open']],
    ['label' => 'Leçons', 'href' => route('student.lessons.index'), 'key' => 'lessons', 'icon' => $navIcons['document']],
    ['label' => 'Activités', 'href' => route('student.activities.index'), 'key' => 'activities', 'icon' => $navIcons['clipboard']],
    ['label' => 'Examens', 'href' => route('student.exams.index'), 'key' => 'exams', 'icon' => $navIcons['document']],
    ['label' => 'Bulletin', 'href' => route('student.bulletin.index'), 'key' => 'bulletin', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label' => 'Horaire', 'href' => route('student.schedule.index'), 'key' => 'schedule', 'icon' => $navIcons['calendar']],
];
@endphp

<nav {{ $attributes->merge(['class' => 'es-dock']) }} aria-label="Navigation principale">
    <div class="es-dock-inner">
        @foreach ($items as $item)
            @php $isActive = $active === $item['key']; @endphp
            <a
                href="{{ $item['href'] }}"
                aria-current="{{ $isActive ? 'page' : 'false' }}"
                @class([
                    'es-dock-item',
                    'es-dock-item-active' => $isActive,
                    'es-dock-item-idle' => ! $isActive,
                ])
            >
                <svg class="es-dock-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span class="es-dock-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
