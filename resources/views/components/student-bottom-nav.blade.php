@props(['active' => null])

@php
$navIcons = config('subjects.icons');

$items = [
    ['label' => 'Accueil', 'href' => route('student.dashboard'), 'key' => 'home', 'icon' => $navIcons['home']],
    ['label' => 'Leçons', 'href' => route('student.lessons.index'), 'key' => 'lessons', 'icon' => $navIcons['document']],
    ['label' => 'Activités', 'href' => route('student.activities.index'), 'key' => 'activities', 'icon' => $navIcons['clipboard']],
    ['label' => 'Projets', 'href' => route('student.projects.index'), 'key' => 'projects', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label' => 'Devoirs', 'href' => route('student.homework.index'), 'key' => 'homework', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
    [
        'label' => 'Points',
        'href' => route('student.points.index'),
        'key' => 'points',
        'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'featured' => true,
    ],
    ['label' => 'Examens', 'href' => route('student.exams.index'), 'key' => 'exams', 'icon' => $navIcons['document']],
    ['label' => 'Horaire', 'href' => route('student.schedule.index'), 'key' => 'schedule', 'icon' => $navIcons['calendar']],
    ['label' => 'Bulletin', 'href' => route('student.bulletin.index'), 'key' => 'bulletin', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
];

$desktopItems = [
    ['label' => 'Accueil', 'href' => route('student.dashboard'), 'key' => 'home'],
    ['label' => 'Points', 'href' => route('student.points.index'), 'key' => 'points', 'highlight' => true],
    ['label' => 'Leçons', 'href' => route('student.lessons.index'), 'key' => 'lessons'],
    ['label' => 'Activités', 'href' => route('student.activities.index'), 'key' => 'activities'],
    ['label' => 'Projets', 'href' => route('student.projects.index'), 'key' => 'projects'],
    ['label' => 'Devoirs', 'href' => route('student.homework.index'), 'key' => 'homework'],
    ['label' => 'Examens', 'href' => route('student.exams.index'), 'key' => 'exams'],
    ['label' => 'Matières', 'href' => route('student.subjects.index'), 'key' => 'subjects'],
    ['label' => 'Horaire', 'href' => route('student.schedule.index'), 'key' => 'schedule'],
    ['label' => 'Bulletin', 'href' => route('student.bulletin.index'), 'key' => 'bulletin'],
];
@endphp

<nav class="hidden lg:block es-student-topnav border-b border-stone-200/80 bg-white/70 backdrop-blur-md" aria-label="Navigation élève">
    <div class="es-container flex flex-wrap items-center gap-1 py-2">
        @foreach ($desktopItems as $item)
            <a
                href="{{ $item['href'] }}"
                @class([
                    'es-student-topnav-link',
                    'es-student-topnav-link-active' => $active === $item['key'],
                    'es-student-topnav-link-points' => ! empty($item['highlight']),
                ])
                @if ($active === $item['key']) aria-current="page" @endif
            >
                @if (! empty($item['highlight']))
                    <span aria-hidden="true">⭐</span>
                @endif
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>

<nav {{ $attributes->merge(['class' => 'es-dock']) }} aria-label="Navigation principale">
    <div class="es-dock-inner">
        @foreach ($items as $item)
            @php $isActive = $active === $item['key']; @endphp
            @if (! empty($item['featured']))
                <a
                    href="{{ $item['href'] }}"
                    aria-current="{{ $isActive ? 'page' : 'false' }}"
                    aria-label="Mes points"
                    @class([
                        'es-dock-star',
                        'es-dock-star-active' => $isActive,
                    ])
                >
                    <span class="es-dock-star-bubble">
                        <x-avatar
                            :name="auth()->user()->student?->full_name ?? auth()->user()->name"
                            :src="auth()->user()->student?->avatarUrl('student')"
                            class="es-dock-feature-avatar"
                        />
                    </span>
                    <span class="es-dock-star-label">{{ $item['label'] }}</span>
                </a>
            @else
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
            @endif
        @endforeach
    </div>
</nav>
