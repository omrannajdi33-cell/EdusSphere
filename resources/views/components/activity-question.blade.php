@props([
    'question',
    'value' => null,
    'namePrefix' => 'responses',
    'readonly' => false,
    'review' => null,
])

@php
$config = $question->config ?? [];
$readonlyAttr = $readonly ? 'disabled' : '';
$showReview = is_array($review) && ($review['gradable'] ?? false);
$isAnswerCorrect = $showReview && ($review['correct'] ?? false);
$correctLabel = $review['correct_label'] ?? null;
$correctIndex = $showReview ? \App\Support\QuestionGrader::correctIndex($question) : null;
$correctIndices = $showReview ? \App\Support\QuestionGrader::correctIndices($question) : [];
$correctBool = $showReview ? \App\Support\QuestionGrader::correctTrueFalse($question) : null;
$blockReviewClass = $showReview
    ? ($isAnswerCorrect ? 'es-question-review-correct' : 'es-question-review-wrong')
    : '';
@endphp

<div
    class="es-question-block rounded-2xl border border-stone-200 bg-white p-5 space-y-4 {{ $blockReviewClass }}"
    data-question-id="{{ $question->id }}"
    data-question-type="{{ $question->type }}"
    role="group"
    aria-labelledby="question-label-{{ $question->id }}"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <p id="question-label-{{ $question->id }}" class="text-base font-extrabold text-es-ink leading-snug">{{ $question->prompt }}</p>
        @if ($showReview)
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide {{ $isAnswerCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $isAnswerCorrect ? '✓ Correct' : '✗ Incorrect' }}
            </span>
        @endif
    </div>

    <div class="space-y-3">
    @switch($question->type)
        @case('mcq')
            @foreach ($config['options'] ?? [] as $index => $option)
                @php
                    $isSelected = (string) $value === (string) $index;
                    $isCorrectOption = $correctIndex !== null && (int) $index === $correctIndex;
                    $optionClass = match (true) {
                        $showReview && $isCorrectOption => 'es-answer-option-correct',
                        $showReview && $isSelected && ! $isCorrectOption => 'es-answer-option-wrong',
                        default => '',
                    };
                @endphp
                <label class="flex items-center gap-3 rounded-xl px-3 py-2 border-2 border-transparent {{ $optionClass }} {{ $showReview ? '' : 'hover:bg-stone-50 cursor-pointer' }}">
                    <input type="radio" name="{{ $namePrefix }}[{{ $question->id }}]" value="{{ $index }}"
                        @checked($isSelected) class="h-5 w-5 text-es-primary" {{ $readonlyAttr }}>
                    <span class="font-medium">{{ $option['text'] ?? '' }}</span>
                </label>
            @endforeach
            @break

        @case('true_false')
            @foreach (['true' => 'Vrai', 'false' => 'Faux'] as $boolVal => $label)
                @php
                    $isSelected = (string) $value === $boolVal;
                    $isCorrectOption = $correctBool !== null && (($boolVal === 'true') === $correctBool);
                    $optionClass = match (true) {
                        $showReview && $isCorrectOption => 'es-answer-option-correct',
                        $showReview && $isSelected && ! $isCorrectOption => 'es-answer-option-wrong',
                        default => '',
                    };
                @endphp
                <label class="flex items-center gap-3 rounded-xl px-3 py-2 border-2 border-transparent {{ $optionClass }} {{ $showReview ? '' : 'hover:bg-stone-50 cursor-pointer' }}">
                    <input type="radio" name="{{ $namePrefix }}[{{ $question->id }}]" value="{{ $boolVal }}"
                        @checked($isSelected) class="h-5 w-5 text-es-primary" {{ $readonlyAttr }}>
                    <span class="font-medium">{{ $label }}</span>
                </label>
            @endforeach
            @break

        @case('multi_select')
            @php $selected = is_array($value) ? $value : []; @endphp
            @foreach ($config['options'] ?? [] as $index => $option)
                @php
                    $isSelected = in_array((string) $index, array_map('strval', $selected), true);
                    $isCorrectOption = in_array((int) $index, $correctIndices, true);
                    $optionClass = match (true) {
                        $showReview && $isCorrectOption => 'es-answer-option-correct',
                        $showReview && $isSelected && ! $isCorrectOption => 'es-answer-option-wrong',
                        default => '',
                    };
                @endphp
                <label class="flex items-center gap-3 rounded-xl px-3 py-2 border-2 border-transparent {{ $optionClass }} {{ $showReview ? '' : 'hover:bg-stone-50 cursor-pointer' }}">
                    <input type="checkbox" name="{{ $namePrefix }}[{{ $question->id }}][]" value="{{ $index }}"
                        @checked($isSelected) class="h-5 w-5 rounded text-es-primary" {{ $readonlyAttr }}>
                    <span class="font-medium">{{ $option['text'] ?? '' }}</span>
                </label>
            @endforeach
            @break

        @case('short_text')
            <input type="text" name="{{ $namePrefix }}[{{ $question->id }}]" value="{{ is_string($value) ? $value : '' }}"
                placeholder="{{ $config['placeholder'] ?? 'Ta réponse…' }}" class="es-input w-full" {{ $readonlyAttr }}>
            @break

        @case('long_text')
            <textarea name="{{ $namePrefix }}[{{ $question->id }}]" rows="4"
                placeholder="{{ $config['placeholder'] ?? 'Écris ta réponse ici…' }}" class="es-textarea w-full" {{ $readonlyAttr }}>{{ is_string($value) ? $value : '' }}</textarea>
            @break

        @case('numeric')
            <input type="number" step="any" name="{{ $namePrefix }}[{{ $question->id }}]" value="{{ $value }}"
                placeholder="Nombre…"
                class="es-input w-full max-w-xs {{ $showReview ? ($isAnswerCorrect ? 'es-input-review-correct' : 'es-input-review-wrong') : '' }}"
                {{ $readonlyAttr }}>
            @break

        @case('fill_blank')
            @php
                $parts = $config['parts'] ?? [];
                $answers = is_array($value) ? $value : [];
            @endphp
            <div class="flex flex-wrap items-center gap-2 text-lg font-medium leading-relaxed">
                @foreach ($parts as $i => $part)
                    <span>{{ $part }}</span>
                    @if ($i < count($parts) - 1)
                        <input type="text" name="{{ $namePrefix }}[{{ $question->id }}][{{ $i }}]"
                            value="{{ $answers[$i] ?? '' }}" class="es-input !w-32 !py-1 inline-block" {{ $readonlyAttr }}>
                    @endif
                @endforeach
            </div>
            @break

        @case('ordering')
            @php
                $items = $config['items'] ?? [];
                $shuffled = collect($items)->shuffle($question->id)->values()->all();
                $orderVal = is_array($value) ? $value : [];
            @endphp
            <p class="text-sm text-es-muted mb-2">Classe du haut vers le bas (1 = premier)</p>
            <div class="space-y-2">
                @foreach ($shuffled as $idx => $item)
                    <div class="flex items-center gap-3 rounded-xl bg-stone-50 px-3 py-2">
                        <input type="number" min="1" max="{{ count($items) }}" name="{{ $namePrefix }}[{{ $question->id }}][{{ $idx }}]"
                            value="{{ $orderVal[$idx] ?? '' }}" class="es-input !w-16 !py-1 text-center" placeholder="#" {{ $readonlyAttr }}>
                        <span class="font-medium">{{ $item }}</span>
                    </div>
                @endforeach
            </div>
            @break

        @case('matching')
            @php
                $left = $config['left'] ?? [];
                $right = $config['right'] ?? [];
                $matchVal = is_array($value) ? $value : [];
            @endphp
            <div class="space-y-3">
                @foreach ($left as $li => $item)
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-semibold min-w-[8rem]">{{ $item }}</span>
                        <span class="text-es-muted">→</span>
                        <select name="{{ $namePrefix }}[{{ $question->id }}][{{ $li }}]" class="es-select flex-1" {{ $readonlyAttr }}>
                            <option value="">Choisir…</option>
                            @foreach ($right as $ri => $rItem)
                                <option value="{{ $ri }}" @selected((string) ($matchVal[$li] ?? '') === (string) $ri)>{{ $rItem }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            @break

        @case('choice_cards')
            @php $cards = $config['cards'] ?? []; @endphp
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($cards as $index => $card)
                    @php
                        $isSelected = (string) $value === (string) $index;
                        $isCorrectOption = $correctIndex !== null && (int) $index === $correctIndex;
                        $ringClass = match (true) {
                            $showReview && $isCorrectOption => 'ring-4 ring-emerald-400 ring-offset-2',
                            $showReview && $isSelected && ! $isCorrectOption => 'ring-4 ring-red-400 ring-offset-2 opacity-80',
                            default => '',
                        };
                    @endphp
                    <label class="cursor-pointer rounded-2xl p-5 text-white font-extrabold text-center shadow-es transition-transform {{ $showReview ? '' : 'hover:scale-[1.02]' }} {{ $ringClass }}"
                        style="background: {{ $card['color'] ?? '#4f46e5' }}">
                        <input type="radio" name="{{ $namePrefix }}[{{ $question->id }}]" value="{{ $index }}"
                            @checked($isSelected) class="sr-only" {{ $readonlyAttr }}>
                        {{ $card['text'] ?? '' }}
                        @if ($showReview && $isCorrectOption)
                            <span class="block text-xs mt-2 opacity-90">Bonne réponse</span>
                        @endif
                    </label>
                @endforeach
            </div>
            @break
    @endswitch
    </div>

    @if ($showReview && ! $isAnswerCorrect && $correctLabel)
        <p class="es-answer-hint text-sm font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            Bonne réponse : {{ $correctLabel }}
        </p>
    @endif
</div>
