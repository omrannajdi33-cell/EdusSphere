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
            'materials' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:5000'],
            'use_custom_time' => ['sometimes', 'boolean'],
            'starts_at' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('use_custom_time')),
                'date_format:H:i',
            ],
            'ends_at' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('use_custom_time')),
                'date_format:H:i',
                'after:starts_at',
            ],
            'activity_ids' => ['nullable', 'array'],
            'activity_ids.*' => ['integer', 'exists:activities,id'],
            'exam_ids' => ['nullable', 'array'],
            'exam_ids.*' => ['integer', 'exists:exams,id'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
            'notion_ids' => ['nullable', 'array'],
            'notion_ids.*' => ['integer', 'exists:notions,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'student_items' => ['nullable', 'array'],
            'student_items.*.student_id' => ['required_with:student_items', 'integer', 'exists:students,id'],
            'student_items.*.item_type' => ['required_with:student_items', 'in:activity,exam,project'],
            'student_items.*.item_id' => ['required_with:student_items', 'integer'],
            'student_items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'use_custom_time' => $this->boolean('use_custom_time'),
            'student_items' => array_values(array_filter((array) $this->input('student_items', []), fn ($item) => ! empty($item['student_id']) && ! empty($item['item_type']) && ! empty($item['item_id']))),
        ]);
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Choisis une matière.',
            'period_number.required' => 'Choisis une période.',
            'day_of_week.required' => 'Choisis un jour de la semaine.',
            'schedule_date.required' => 'Choisis une date.',
            'starts_at.required' => 'Indique une heure de début.',
            'ends_at.required' => 'Indique une heure de fin.',
            'ends_at.after' => 'L\'heure de fin doit être après le début.',
        ];
    }
}
