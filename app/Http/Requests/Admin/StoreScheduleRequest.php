<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $maxPeriod = config('schedule.periods_per_day', 4);

        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'period_number' => ['required', 'integer', 'min:1', 'max:'.$maxPeriod],
            'mode' => ['required', Rule::in(['recurring', 'specific'])],
            'day_of_week' => [
                Rule::requiredIf(fn () => $this->input('mode') === 'recurring'),
                'nullable',
                'integer',
                'min:1',
                'max:7',
            ],
            'schedule_date' => [
                Rule::requiredIf(fn () => $this->input('mode') === 'specific'),
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Choisis une matière.',
            'period_number.required' => 'Choisis une période.',
            'day_of_week.required' => 'Choisis un jour de la semaine.',
            'schedule_date.required' => 'Choisis une date.',
        ];
    }
}
