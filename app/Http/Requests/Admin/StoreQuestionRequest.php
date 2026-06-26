<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $types = array_keys(config('activity.question_types', []));

        return [
            'type' => ['required', Rule::in($types)],
            'prompt' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string', 'max:500'],
            'correct_option' => ['nullable', 'integer', 'min:0'],
            'correct_options' => ['nullable', 'array'],
            'correct_options.*' => ['integer', 'min:0'],
            'correct_bool' => ['nullable', Rule::in(['true', 'false'])],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'correct_number' => ['nullable', 'numeric'],
            'tolerance' => ['nullable', 'numeric', 'min:0'],
            'blank_sentence' => ['nullable', 'string'],
            'blank_answers' => ['nullable', 'array'],
            'blank_answers.*' => ['nullable', 'string', 'max:255'],
            'order_items' => ['nullable', 'array'],
            'order_items.*' => ['nullable', 'string', 'max:255'],
            'match_left' => ['nullable', 'array'],
            'match_left.*' => ['nullable', 'string', 'max:255'],
            'match_right' => ['nullable', 'array'],
            'match_right.*' => ['nullable', 'string', 'max:255'],
            'cards' => ['nullable', 'array'],
            'cards.*.text' => ['nullable', 'string', 'max:255'],
            'cards.*.color' => ['nullable', 'string', 'max:20'],
            'correct_card' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
