@props(['step' => 1, 'activity' => null])

@php
$steps = [
    1 => ['label' => 'Informations', 'desc' => 'Titre et matière'],
    2 => ['label' => 'Contenu', 'desc' => 'PDF, écriture ou questions'],
    3 => ['label' => 'Publication', 'desc' => 'Vérifier et publier'],
];
@endphp

<nav aria-label="Étapes de création" class="es-wizard-nav mb-10">
    <ol class="es-wizard-track">
        @foreach ($steps as $num => $meta)
            @php
                $done = $step > $num;
                $active = $step === $num;
                $href = $activity?->exists
                    ? match ($num) {
                        1 => route('admin.activities.build', ['activity' => $activity, 'step' => 1]),
                        2 => route('admin.activities.build', ['activity' => $activity, 'step' => 2]),
                        3 => route('admin.activities.build', ['activity' => $activity, 'step' => 3]),
                    }
                    : null;
            @endphp
            <li class="es-wizard-step {{ $active ? 'es-wizard-step-active' : '' }} {{ $done ? 'es-wizard-step-done' : '' }}">
                @if ($href && ($done || $active))
                    <a href="{{ $href }}" class="es-wizard-step-link">
                        <span class="es-wizard-badge">{{ $done ? '✓' : $num }}</span>
                        <span class="es-wizard-text">
                            <span class="es-wizard-label">{{ $meta['label'] }}</span>
                            <span class="es-wizard-desc">{{ $meta['desc'] }}</span>
                        </span>
                    </a>
                @else
                    <span class="es-wizard-step-link es-wizard-step-idle">
                        <span class="es-wizard-badge">{{ $num }}</span>
                        <span class="es-wizard-text">
                            <span class="es-wizard-label">{{ $meta['label'] }}</span>
                            <span class="es-wizard-desc">{{ $meta['desc'] }}</span>
                        </span>
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
