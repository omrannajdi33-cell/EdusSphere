@props(['content' => ''])

@php
    $intro = '';
    $sections = [];
    $currentIndex = null;

    foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
        if (str_starts_with($line, '## ')) {
            $sections[] = ['heading' => substr($line, 3), 'body' => ''];
            $currentIndex = count($sections) - 1;
            continue;
        }

        if ($currentIndex === null) {
            $intro .= $line."\n";
        } else {
            $sections[$currentIndex]['body'] .= $line."\n";
        }
    }

    $intro = trim($intro);
@endphp

<div {{ $attributes->merge(['class' => 'es-lesson-content space-y-5']) }}>
    @if ($intro !== '')
        <div class="es-lesson-content-intro rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100/80 p-5 text-base leading-relaxed whitespace-pre-wrap font-semibold text-es-ink">
            {{ $intro }}
        </div>
    @endif

    @foreach ($sections as $section)
        <section class="es-lesson-content-section rounded-2xl border border-stone-200 bg-white/80 p-5 shadow-sm">
            <h2 class="text-lg font-extrabold text-es-ink mb-3 flex items-center gap-2">
                {{ $section['heading'] }}
            </h2>
            <div class="text-base leading-relaxed whitespace-pre-wrap text-es-ink/90">{{ trim($section['body']) }}</div>
        </section>
    @endforeach
</div>
