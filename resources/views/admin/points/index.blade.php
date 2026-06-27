@extends('layouts.admin')

@section('admin-content')
<div
    class="es-page-enter"
    x-data="behaviorPointsBoard({
        storeUrl: @js(route('admin.points.store')),
        csrf: @js(csrf_token()),
        positive: @js($positiveActions->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'value' => $a->value, 'description' => $a->description])),
        negative: @js($negativeActions->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'value' => $a->value, 'description' => $a->description])),
    })"
>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Points comportement</h1>
            <p class="es-page-subtitle">Attribue des bons ou mauvais points à chaque élève</p>
        </div>
        <x-button href="{{ route('admin.points.settings') }}" variant="secondary">Paramètres actions & récompenses</x-button>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <x-card class="mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <input type="search" name="q" value="{{ $search }}" placeholder="Rechercher un élève…" class="es-input flex-1">
            <select name="level" class="es-select sm:w-44">
                <option value="">Tous les niveaux</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}" @selected($levelFilter == $level->id)>{{ $level->name }}</option>
                @endforeach
            </select>
            <select name="class" class="es-select sm:w-44">
                <option value="">Toutes les classes</option>
                @foreach ($classGroups as $group)
                    <option value="{{ $group->id }}" @selected($classFilter == $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filtrer</x-button>
        </form>
    </x-card>

    @if ($positiveActions->isEmpty() && $negativeActions->isEmpty())
        <x-alert type="warning" title="Aucune action configurée">
            Configure les actions dans les <a href="{{ route('admin.points.settings') }}" class="underline font-bold">paramètres</a>.
        </x-alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($students as $student)
            <button
                type="button"
                class="es-behavior-student-card"
                @click="openStudent(@js([
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'avatar' => $student->avatarUrl('admin'),
                    'total' => (int) ($student->points_total ?? 0),
                ]))"
            >
                <x-avatar
                    :name="$student->full_name"
                    :src="$student->avatarUrl('admin')"
                    size="xl"
                    class="mx-auto mb-3"
                />
                <p class="font-extrabold text-es-ink text-center truncate">{{ $student->full_name }}</p>
                <p class="text-xs text-es-muted text-center mt-1 truncate">
                    {{ $student->schoolLevel?->name }}
                    @if ($student->classGroup) · {{ $student->classGroup->name }} @endif
                </p>
                <p
                    class="es-behavior-total mt-3 text-center"
                    :class="totals[{{ $student->id }}] < 0 ? 'text-red-600' : 'text-emerald-600'"
                    x-text="formatTotal(totals[{{ $student->id }}] ?? {{ (int) ($student->points_total ?? 0) }})"
                >
                    {{ ($student->points_total ?? 0) >= 0 ? '+' : '' }}{{ (int) ($student->points_total ?? 0) }} pts
                </p>
            </button>
        @empty
            <div class="sm:col-span-2 lg:col-span-4 es-empty">
                <p class="font-extrabold text-es-ink">Aucun élève</p>
                <p class="text-es-muted mt-2">Ajoute des élèves pour attribuer des points.</p>
                <x-button href="{{ route('admin.students.create') }}" class="mt-4">+ Ajouter un élève</x-button>
            </div>
        @endforelse
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="es-behavior-modal-wrap"
            @keydown.escape.window="close()"
            role="dialog"
            aria-modal="true"
            :aria-label="selected ? 'Points pour ' + selected.name : 'Attribuer des points'"
        >
            <div class="es-behavior-modal-backdrop" @click="close()"></div>
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="es-behavior-modal-panel"
            >
                <template x-if="selected">
                    <div class="es-behavior-modal-body">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="shrink-0">
                                <template x-if="selected.avatar">
                                    <img
                                        :src="selected.avatar"
                                        :alt="selected.name"
                                        class="es-avatar es-avatar-xl object-cover ring-4 ring-white shadow-es"
                                    >
                                </template>
                                <template x-if="!selected.avatar">
                                    <span
                                        class="es-avatar es-avatar-xl ring-4 ring-white shadow-es"
                                        x-text="selected.name?.charAt(0) ?? '?'"
                                    ></span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <h3 class="text-2xl font-black text-es-ink truncate" x-text="selected.name"></h3>
                                <p class="text-lg font-bold mt-1" :class="currentTotal < 0 ? 'text-red-600' : 'text-emerald-600'" x-text="formatTotal(currentTotal)"></p>
                            </div>
                            <button type="button" class="es-btn es-btn-secondary es-btn-sm shrink-0" @click="close()">Fermer</button>
                        </div>

                        <p x-show="feedback" x-text="feedback" class="mb-4 text-sm font-bold text-emerald-700 bg-emerald-50 rounded-xl px-4 py-2"></p>

                        <div class="es-behavior-columns">
                            <div class="es-behavior-column es-behavior-column-good">
                                <h4 class="es-behavior-column-title text-emerald-800">Bons points</h4>
                                <ul class="space-y-2 flex-1">
                                    <template x-for="action in positive" :key="action.id">
                                        <li>
                                            <button
                                                type="button"
                                                class="es-behavior-action es-behavior-action-good w-full"
                                                :disabled="loading"
                                                @click="award(action.id)"
                                            >
                                                <span class="font-extrabold shrink-0" x-text="'+' + action.value"></span>
                                                <span class="flex-1 min-w-0 text-left">
                                                    <span class="block font-bold" x-text="action.name"></span>
                                                    <span class="block text-xs opacity-80" x-text="action.description" x-show="action.description"></span>
                                                </span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="es-behavior-column es-behavior-column-bad">
                                <h4 class="es-behavior-column-title text-red-800">Points à retirer</h4>
                                <ul class="space-y-2 flex-1">
                                    <template x-for="action in negative" :key="action.id">
                                        <li>
                                            <button
                                                type="button"
                                                class="es-behavior-action es-behavior-action-bad w-full"
                                                :disabled="loading"
                                                @click="award(action.id)"
                                            >
                                                <span class="font-extrabold shrink-0" x-text="action.value"></span>
                                                <span class="flex-1 min-w-0 text-left">
                                                    <span class="block font-bold" x-text="action.name"></span>
                                                    <span class="block text-xs opacity-80" x-text="action.description" x-show="action.description"></span>
                                                </span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function behaviorPointsBoard(config) {
    const initialTotals = {};
    @foreach ($students as $student)
        initialTotals[{{ $student->id }}] = {{ (int) ($student->points_total ?? 0) }};
    @endforeach

    return {
        open: false,
        loading: false,
        feedback: '',
        selected: null,
        positive: config.positive,
        negative: config.negative,
        totals: initialTotals,
        get currentTotal() {
            return this.selected ? (this.totals[this.selected.id] ?? 0) : 0;
        },
        formatTotal(value) {
            const n = Number(value) || 0;
            return (n > 0 ? '+' : '') + n + ' pts';
        },
        openStudent(student) {
            this.selected = student;
            this.feedback = '';
            this.open = true;
        },
        close() {
            this.open = false;
            this.selected = null;
            this.feedback = '';
        },
        async award(actionId) {
            if (!this.selected || this.loading) return;
            this.loading = true;
            this.feedback = '';
            try {
                const res = await fetch(config.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify({
                        student_id: this.selected.id,
                        point_action_id: actionId,
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Erreur');
                this.totals[this.selected.id] = data.total;
                this.selected.total = data.total;
                this.feedback = data.message;
            } catch (e) {
                this.feedback = 'Impossible d\'attribuer le point. Réessaie.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
