@props(['compact' => false])

@php $cardColors = ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e']; @endphp

<div x-data="{ qType: 'mcq' }" @class(['mt-3 pt-3 border-t border-stone-100', 'mt-4 pt-4' => ! $compact])>
    <p @class(['font-bold text-es-muted mb-2', 'text-xs' => $compact, 'text-sm' => ! $compact])>Nouvelle question</p>

    @if ($compact)
        <div class="mb-3">
            <label class="es-label text-xs">Format</label>
            <select x-model="qType" class="es-select es-select-sm text-sm">
                @foreach ($questionTypes as $key => $meta)
                    <option value="{{ $key }}">{{ $meta['icon'] ?? '' }} {{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
            @foreach ($questionTypes as $key => $meta)
                <button type="button" @click="qType = '{{ $key }}'" class="es-qtype-chip" :class="qType === '{{ $key }}' ? 'es-qtype-chip-active' : ''">
                    <span>{{ $meta['icon'] ?? '?' }}</span><span>{{ $meta['label'] }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.exams.questions.store', [$exam, $page]) }}" @class(['space-y-3', 'space-y-4' => ! $compact])>
        @csrf
        <input type="hidden" name="type" :value="qType">

        <x-input label="Énoncé" name="prompt" required placeholder="Ta question…" :class="$compact ? '!py-2 text-sm' : ''"/>

        <div x-show="qType === 'mcq'" x-cloak class="space-y-2">
            @for ($i = 0; $i < ($compact ? 3 : 4); $i++)
                <x-input :label="'Choix '.($i + 1)" :name="'options['.$i.'][text]'" :class="$compact ? '!py-1.5 text-sm' : ''"/>
            @endfor
            <x-input label="Bonne réponse (0 = 1er)" name="correct_option" type="number" min="0" max="3" value="0" :class="$compact ? '!py-1.5 text-sm' : ''"/>
        </div>

        <div x-show="qType === 'true_false'" x-cloak>
            <label class="es-label text-xs">Bonne réponse</label>
            <select name="correct_bool" class="es-select es-select-sm"><option value="true">Vrai</option><option value="false">Faux</option></select>
        </div>

        <div x-show="qType === 'multi_select'" x-cloak class="space-y-2">
            @for ($i = 0; $i < 3; $i++)
                <x-input :label="'Option '.($i + 1)" :name="'options['.$i.'][text]'" :class="$compact ? '!py-1.5 text-sm' : ''"/>
            @endfor
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                @for ($i = 0; $i < 3; $i++)
                    <label class="flex items-center gap-1"><input type="checkbox" name="correct_options[]" value="{{ $i }}" class="rounded"> #{{ $i + 1 }}</label>
                @endfor
            </div>
        </div>

        <div x-show="qType === 'numeric'" x-cloak class="grid grid-cols-2 gap-2">
            <x-input label="Réponse" name="correct_number" type="number" step="any" :class="$compact ? '!py-1.5 text-sm' : ''"/>
            <x-input label="± tol." name="tolerance" type="number" step="any" value="0" :class="$compact ? '!py-1.5 text-sm' : ''"/>
        </div>

        <div x-show="qType === 'choice_cards'" x-cloak class="space-y-2">
            @foreach (array_slice($cardColors, 0, 4) as $i => $color)
                <x-input :label="'Carte '.($i + 1)" :name="'cards['.$i.'][text]'" :class="$compact ? '!py-1.5 text-sm' : ''"/>
                <input type="hidden" name="cards[{{ $i }}][color]" value="{{ $color }}">
            @endforeach
            <x-input label="Bonne carte (0-3)" name="correct_card" type="number" min="0" max="3" value="0" :class="$compact ? '!py-1.5 text-sm' : ''"/>
        </div>

        <div x-show="qType === 'short_text' || qType === 'long_text'" x-cloak>
            <x-input label="Indice" name="placeholder" :class="$compact ? '!py-1.5 text-sm' : ''"/>
        </div>

        <x-button type="submit" class="es-btn-sm w-full sm:w-auto">+ Ajouter</x-button>
    </form>
</div>
