<?php

namespace App\Http\Controllers\Admin;

trait BuildsQuestionConfig
{
    protected function buildQuestionConfig(array $data): array
    {
        return match ($data['type']) {
            'mcq' => [
                'options' => array_values(array_filter($data['options'] ?? [], fn ($o) => filled($o['text'] ?? null))),
                'correct' => (int) ($data['correct_option'] ?? 0),
            ],
            'true_false' => [
                'correct' => ($data['correct_bool'] ?? 'true') === 'true',
            ],
            'multi_select' => [
                'options' => array_values(array_filter($data['options'] ?? [], fn ($o) => filled($o['text'] ?? null))),
                'correct' => array_map('intval', $data['correct_options'] ?? []),
            ],
            'short_text', 'long_text' => [
                'placeholder' => $data['placeholder'] ?? '',
            ],
            'numeric' => [
                'correct' => isset($data['correct_number']) ? (float) $data['correct_number'] : null,
                'tolerance' => (float) ($data['tolerance'] ?? 0),
            ],
            'fill_blank' => $this->buildFillBlankConfig($data),
            'ordering' => [
                'items' => array_values(array_filter($data['order_items'] ?? [], fn ($t) => filled($t))),
            ],
            'matching' => [
                'left' => array_values(array_filter($data['match_left'] ?? [], fn ($t) => filled($t))),
                'right' => array_values(array_filter($data['match_right'] ?? [], fn ($t) => filled($t))),
            ],
            'choice_cards' => [
                'cards' => array_values(array_filter($data['cards'] ?? [], fn ($c) => filled($c['text'] ?? null))),
                'correct' => (int) ($data['correct_card'] ?? 0),
            ],
            default => [],
        };
    }

    protected function buildFillBlankConfig(array $data): array
    {
        $sentence = $data['blank_sentence'] ?? '';
        $parts = preg_split('/_{3,}/', $sentence) ?: [$sentence];
        $answers = array_values(array_filter($data['blank_answers'] ?? [], fn ($a) => filled($a)));

        return [
            'parts' => $parts,
            'answers' => $answers,
            'display' => $sentence,
        ];
    }
}
