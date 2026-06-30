<?php

namespace App\Http\Requests\Admin;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityHomeworkRules
{
    /** @return array<string, mixed> */
    public static function rules(FormRequest $request): array
    {
        return [
            'is_homework' => ['sometimes', 'boolean'],
            'require_result_photo' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'required_if:is_homework,1,true', 'date'],
            'homework_slot' => [
                'nullable',
                'required_if:is_homework,1,true',
                Rule::in([Activity::HOMEWORK_DURING_SCHOOL, Activity::HOMEWORK_AFTER_SCHOOL]),
            ],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'due_at.required_if' => 'Indique une date limite pour ce devoir.',
            'homework_slot.required_if' => 'Choisis si le devoir se fait pendant ou après l\'école.',
            'homework_slot.in' => 'Choisis « Pendant l\'école » ou « Après l\'école ».',
        ];
    }

    /** @param  array<string, mixed>  $validated */
    public static function normalize(array $validated): array
    {
        $isHomework = (bool) ($validated['is_homework'] ?? false);

        if (! $isHomework) {
            $validated['is_homework'] = false;
            $validated['due_at'] = null;
            $validated['homework_slot'] = null;

            return $validated;
        }

        $validated['is_homework'] = true;

        return $validated;
    }
}
