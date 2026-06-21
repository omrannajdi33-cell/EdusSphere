@props(['active' => null])

@php
$items = [
    ['label' => 'Accueil', 'href' => url('/student'), 'key' => 'home'],
    ['label' => 'Matières', 'href' => url('/student/subjects'), 'key' => 'subjects'],
    ['label' => 'Leçons', 'href' => url('/student/lessons'), 'key' => 'lessons'],
    ['label' => 'Activités', 'href' => url('/student/activities'), 'key' => 'activities'],
    ['label' => 'Examens', 'href' => url('/student/exams'), 'key' => 'exams'],
    ['label' => 'Horaire', 'href' => url('/student/schedule'), 'key' => 'schedule'],
    ['label' => 'Points', 'href' => url('/student/points'), 'key' => 'points'],
];
@endphp

<nav {{ $attributes->merge(['class' => 'fixed bottom-0 inset-x-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur md:hidden']) }}>
    <div class="flex justify-around px-1 py-2">
        @foreach ($items as $item)
            @php $isActive = $active === $item['key']; @endphp
            <a
                href="{{ $item['href'] }}"
                class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-[10px] font-medium {{ $isActive ? 'text-indigo-600' : 'text-slate-500' }}"
            >
                <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-indigo-600' : 'bg-transparent' }}"></span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
