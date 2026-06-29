@props(['step', 'project'])

@php $isNew = ! $project->exists; @endphp

<nav class="es-wizard-nav mb-8" aria-label="Étapes du projet">
    <ol class="flex flex-wrap gap-2">
        @foreach ([1 => 'Informations & bulletin', 2 => 'Consignes & documents', 3 => 'Publication'] as $num => $label)
            @php
                $href = $isNew && $num > 1
                    ? null
                    : ($isNew ? route('admin.projects.create') : route('admin.projects.build', ['project' => $project, 'step' => $num]));
                $active = $step === $num;
                $done = $step > $num;
            @endphp
            <li>
                @if ($href)
                    <a href="{{ $href }}" @class(['es-wizard-step', 'es-wizard-step-active' => $active, 'es-wizard-step-done' => $done])>
                        <span class="es-wizard-step-num">{{ $num }}</span>
                        {{ $label }}
                    </a>
                @else
                    <span @class(['es-wizard-step', 'es-wizard-step-disabled' => true])>
                        <span class="es-wizard-step-num">{{ $num }}</span>
                        {{ $label }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
