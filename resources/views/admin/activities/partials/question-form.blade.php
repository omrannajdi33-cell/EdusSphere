@php
$cardColors = ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e', '#0ea5e9', '#8b5cf6'];
@endphp

<div x-data="{ qType: 'mcq' }" class="mt-4 pt-4 border-t border-stone-100">
    <p class="text-sm font-bold text-es-muted mb-3">Nouvelle question — choisis un format :</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-5">
        @foreach ($questionTypes as $key => $meta)
            <button type="button" @click="qType = '{{ $key }}'"
                class="es-qtype-chip"
                :class="qType === '{{ $key }}' ? 'es-qtype-chip-active' : ''">
                <span>{{ $meta['icon'] ?? '?' }}</span>
                <span>{{ $meta['label'] }}</span>
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.activities.questions.store', [$activity, $page]) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="type" :value="qType">

        <x-input label="Énoncé" name="prompt" required placeholder="Ta question…"/>

        <div x-show="qType === 'mcq'" x-cloak class="space-y-2">
            @for ($i = 0; $i < 4; $i++)
                <x-input :label="'Choix '.($i + 1)" :name="'options['.$i.'][text]'" />
            @endfor
            <x-input label="Bonne réponse (0 = 1er choix)" name="correct_option" type="number" min="0" max="3" value="0"/>
        </div>

        <div x-show="qType === 'true_false'" x-cloak>
            <label class="es-label">Bonne réponse</label>
            <select name="correct_bool" class="es-select"><option value="true">Vrai</option><option value="false">Faux</option></select>
        </div>

        <div x-show="qType === 'multi_select'" x-cloak class="space-y-2">
            @for ($i = 0; $i < 4; $i++)
                <x-input :label="'Option '.($i + 1)" :name="'options['.$i.'][text]'" />
            @endfor
            <div class="flex flex-wrap gap-3 pt-1">
                @for ($i = 0; $i < 4; $i++)
                    <label class="flex items-center gap-2 text-sm font-semibold">
                        <input type="checkbox" name="correct_options[]" value="{{ $i }}" class="rounded"> Bonne #{{ $i + 1 }}
                    </label>
                @endfor
            </div>
        </div>

        <div x-show="qType === 'short_text' || qType === 'long_text'" x-cloak>
            <x-input label="Indice (optionnel)" name="placeholder"/>
        </div>

        <div x-show="qType === 'numeric'" x-cloak class="grid gap-3 sm:grid-cols-2">
            <x-input label="Bonne réponse" name="correct_number" type="number" step="any"/>
            <x-input label="Tolérance ±" name="tolerance" type="number" step="any" value="0"/>
        </div>

        <div x-show="qType === 'fill_blank'" x-cloak class="space-y-2">
            <textarea name="blank_sentence" class="es-textarea" rows="2" placeholder="Le ___ brille dans le ___."></textarea>
            @for ($i = 0; $i < 4; $i++)
                <x-input :label="'Réponse trou '.($i + 1)" :name="'blank_answers['.$i.']'" />
            @endfor
        </div>

        <div x-show="qType === 'ordering'" x-cloak class="space-y-2">
            @for ($i = 0; $i < 5; $i++)
                <x-input :label="'Élément '.($i + 1).' (ordre correct)'" :name="'order_items['.$i.']'" />
            @endfor
        </div>

        <div x-show="qType === 'matching'" x-cloak class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <p class="text-xs font-bold uppercase text-es-muted">Colonne A</p>
                @for ($i = 0; $i < 4; $i++) <x-input :name="'match_left['.$i.']'"/> @endfor
            </div>
            <div class="space-y-2">
                <p class="text-xs font-bold uppercase text-es-muted">Colonne B</p>
                @for ($i = 0; $i < 4; $i++) <x-input :name="'match_right['.$i.']'"/> @endfor
            </div>
        </div>

        <div x-show="qType === 'choice_cards'" x-cloak class="space-y-2">
            @for ($i = 0; $i < 4; $i++)
                <div class="flex gap-2 items-end">
                    <x-input :label="'Carte '.($i + 1)" :name="'cards['.$i.'][text]'" class="flex-1"/>
                    <input type="hidden" name="cards[{{ $i }}][color]" value="{{ $cardColors[$i] }}">
                    <span class="h-10 w-10 rounded-xl mb-1 shrink-0" style="background:{{ $cardColors[$i] }}"></span>
                </div>
            @endfor
            <x-input label="Bonne carte (0 = 1ère)" name="correct_card" type="number" min="0" max="3" value="0"/>
        </div>

        <x-button type="submit" variant="secondary" class="es-btn-sm">Ajouter la question</x-button>
    </form>
</div>
