@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="es-page-title">Paramètres des points</h1>
            <p class="es-page-subtitle">Actions personnalisées et récompenses échangeables</p>
        </div>
        <x-button href="{{ route('admin.points.index') }}" variant="secondary">← Retour aux élèves</x-button>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" class="mb-6" title="Erreur">
            <ul class="list-disc list-inside text-sm mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid gap-8 lg:grid-cols-2 mb-8">
        @foreach (['positive' => ['label' => 'Actions positives', 'class' => 'es-behavior-section-good', 'actions' => $positiveActions], 'negative' => ['label' => 'Actions négatives', 'class' => 'es-behavior-section-bad', 'actions' => $negativeActions]] as $type => $section)
            <x-card>
                <h2 class="es-behavior-section-title {{ $section['class'] }} mb-4">{{ $section['label'] }}</h2>

                <ul class="space-y-3 mb-6">
                    @forelse ($section['actions'] as $action)
                        <li class="es-point-settings-row">
                            <details class="group">
                                <summary class="flex items-center gap-3 cursor-pointer list-none">
                                    <span @class([
                                        'es-behavior-history-badge shrink-0',
                                        'es-behavior-history-badge-good' => $action->isPositive(),
                                        'es-behavior-history-badge-bad' => $action->isNegative(),
                                    ])>
                                        {{ $action->value >= 0 ? '+' : '' }}{{ $action->value }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-extrabold text-es-ink">{{ $action->name }}</p>
                                        @if ($action->description)
                                            <p class="text-xs text-es-muted truncate">{{ $action->description }}</p>
                                        @endif
                                    </div>
                                    @unless ($action->is_active)
                                        <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg">Inactive</span>
                                    @endunless
                                    <span class="text-xs font-bold text-es-muted group-open:hidden">Modifier</span>
                                </summary>

                                <form method="POST" action="{{ route('admin.point-actions.update', $action) }}" class="mt-4 pt-4 border-t border-stone-100 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <div>
                                        <label class="es-label">Nom</label>
                                        <input type="text" name="name" value="{{ old('name', $action->name) }}" class="es-input" required maxlength="100">
                                    </div>
                                    <div>
                                        <label class="es-label">Description</label>
                                        <input type="text" name="description" value="{{ old('description', $action->description) }}" class="es-input" maxlength="255">
                                    </div>
                                    <div>
                                        <label class="es-label">Points</label>
                                        <input type="number" name="magnitude" value="{{ old('magnitude', abs($action->value)) }}" class="es-input" min="1" max="100" required>
                                    </div>
                                    <label class="flex items-center gap-2 text-sm font-bold text-es-ink">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $action->is_active)) class="rounded border-stone-300">
                                        Active
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        <x-button type="submit" class="es-btn-sm">Enregistrer</x-button>
                                    </div>
                                </form>

                                @if ($action->is_active)
                                    <form method="POST" action="{{ route('admin.point-actions.destroy', $action) }}" class="mt-2" onsubmit="return confirm('Désactiver cette action ?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" class="es-btn-sm">Désactiver</x-button>
                                    </form>
                                @endif
                            </details>
                        </li>
                    @empty
                        <li class="text-sm text-es-muted">Aucune action pour le moment.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.point-actions.store') }}" class="es-point-settings-add space-y-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <p class="text-sm font-extrabold text-es-ink">+ Nouvelle action</p>
                    <input type="text" name="name" placeholder="Nom (ex. Aide un camarade)" class="es-input" required maxlength="100">
                    <input type="text" name="description" placeholder="Description (optionnel)" class="es-input" maxlength="255">
                    <input type="number" name="magnitude" placeholder="Points" class="es-input" min="1" max="100" value="1" required>
                    <x-button type="submit" variant="secondary" class="es-btn-sm">Ajouter</x-button>
                </form>
            </x-card>
        @endforeach
    </div>

    <x-card title="Récompenses échangeables">
        <p class="text-sm text-es-muted mb-6">Les élèves dépensent leurs points pour obtenir ces récompenses. Tu es notifié à chaque échange.</p>

        <ul class="grid gap-4 sm:grid-cols-2 mb-8">
            @forelse ($rewards as $reward)
                <li class="es-behavior-reward-card">
                    <details class="group">
                        <summary class="cursor-pointer list-none">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-extrabold text-lg text-es-ink">{{ $reward->name }}</p>
                                    @if ($reward->description)
                                        <p class="text-sm text-es-muted mt-1">{{ $reward->description }}</p>
                                    @endif
                                </div>
                                <span class="es-behavior-reward-cost shrink-0">{{ $reward->cost }} pts</span>
                            </div>
                            @unless ($reward->is_active)
                                <span class="inline-block mt-2 text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg">Inactive</span>
                            @endunless
                            <span class="block mt-2 text-xs font-bold text-indigo-600 group-open:hidden">Modifier</span>
                        </summary>

                        <form method="POST" action="{{ route('admin.point-rewards.update', $reward) }}" class="mt-4 pt-4 border-t border-indigo-100/80 space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="es-label">Nom</label>
                                <input type="text" name="name" value="{{ old('name', $reward->name) }}" class="es-input" required maxlength="120">
                            </div>
                            <div>
                                <label class="es-label">Description</label>
                                <input type="text" name="description" value="{{ old('description', $reward->description) }}" class="es-input" maxlength="255">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="es-label">Coût (pts)</label>
                                    <input type="number" name="cost" value="{{ old('cost', $reward->cost) }}" class="es-input" min="1" max="10000" required>
                                </div>
                                <div>
                                    <label class="es-label">Ordre</label>
                                    <input type="number" name="display_order" value="{{ old('display_order', $reward->display_order) }}" class="es-input" min="0" max="999">
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-sm font-bold text-es-ink">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $reward->is_active)) class="rounded border-stone-300">
                                Active
                            </label>
                            <x-button type="submit" class="es-btn-sm">Enregistrer</x-button>
                        </form>

                        @if ($reward->is_active)
                            <form method="POST" action="{{ route('admin.point-rewards.destroy', $reward) }}" class="mt-2" onsubmit="return confirm('Désactiver cette récompense ?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" class="es-btn-sm">Désactiver</x-button>
                            </form>
                        @endif
                    </details>
                </li>
            @empty
                <li class="sm:col-span-2 text-sm text-es-muted">Aucune récompense configurée.</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('admin.point-rewards.store') }}" class="es-point-settings-add space-y-3 max-w-xl">
            @csrf
            <p class="text-sm font-extrabold text-es-ink">+ Nouvelle récompense</p>
            <input type="text" name="name" placeholder="Nom (ex. 5 min de jeu)" class="es-input" required maxlength="120">
            <input type="text" name="description" placeholder="Description (optionnel)" class="es-input" maxlength="255">
            <div class="grid grid-cols-2 gap-3">
                <input type="number" name="cost" placeholder="Coût en points" class="es-input" min="1" max="10000" value="10" required>
                <input type="number" name="display_order" placeholder="Ordre" class="es-input" min="0" max="999" value="0">
            </div>
            <x-button type="submit" variant="secondary" class="es-btn-sm">Ajouter la récompense</x-button>
        </form>
    </x-card>
</div>
@endsection
