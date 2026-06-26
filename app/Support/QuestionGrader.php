<?php

namespace App\Support;

class QuestionGrader
{
    /** @var list<string> */
    public const AUTO_GRADABLE = ['mcq', 'true_false', 'multi_select', 'numeric', 'choice_cards'];

    public static function isAutoGradable(string $type): bool
    {
        return in_array($type, self::AUTO_GRADABLE, true);
    }

    /**
     * @return array{gradable: bool, correct: bool|null, correct_label: string|null}
     */
    public static function evaluate(object $question, mixed $value): array
    {
        if (! self::isAutoGradable($question->type)) {
            return ['gradable' => false, 'correct' => null, 'correct_label' => null];
        }

        $correct = self::isCorrect($question, $value);

        return [
            'gradable' => true,
            'correct' => $correct,
            'correct_label' => self::formatCorrectAnswer($question),
        ];
    }

    public static function isCorrect(object $question, mixed $value): bool
    {
        $config = $question->config ?? [];

        return match ($question->type) {
            'mcq', 'choice_cards' => (int) $value === (int) ($config['correct'] ?? -1),
            'true_false' => ($value === 'true') === (bool) ($config['correct'] ?? false),
            'multi_select' => self::arraysEqual(
                array_map('intval', is_array($value) ? $value : []),
                array_map('intval', is_array($config['correct'] ?? null) ? $config['correct'] : []),
            ),
            'numeric' => self::numericMatch($value, $config['correct'] ?? null, $config),
            default => false,
        };
    }

    public static function formatCorrectAnswer(object $question): ?string
    {
        $config = $question->config ?? [];

        return match ($question->type) {
            'mcq' => $config['options'][$config['correct'] ?? -1]['text'] ?? null,
            'choice_cards' => $config['cards'][$config['correct'] ?? -1]['text'] ?? null,
            'true_false' => ($config['correct'] ?? false) ? 'Vrai' : 'Faux',
            'multi_select' => collect(is_array($config['correct'] ?? null) ? $config['correct'] : [])
                ->map(fn ($index) => $config['options'][$index]['text'] ?? null)
                ->filter()
                ->implode(', ') ?: null,
            'numeric' => isset($config['correct']) ? (string) $config['correct'] : null,
            default => null,
        };
    }

    public static function correctIndex(object $question): ?int
    {
        if (! in_array($question->type, ['mcq', 'choice_cards'], true)) {
            return null;
        }

        $index = $question->config['correct'] ?? null;

        return is_numeric($index) ? (int) $index : null;
    }

    /** @return list<int> */
    public static function correctIndices(object $question): array
    {
        if ($question->type !== 'multi_select') {
            return [];
        }

        return array_map('intval', is_array($question->config['correct'] ?? null) ? $question->config['correct'] : []);
    }

    public static function correctTrueFalse(object $question): ?bool
    {
        if ($question->type !== 'true_false') {
            return null;
        }

        return (bool) ($question->config['correct'] ?? false);
    }

    /** @param list<int|string> $a @param list<int|string> $b */
    private static function arraysEqual(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }

    private static function numericMatch(mixed $value, mixed $expected, array $config): bool
    {
        if (! is_numeric($value) || ! is_numeric($expected)) {
            return false;
        }

        return abs((float) $value - (float) $expected) <= (float) ($config['tolerance'] ?? 0);
    }
}
