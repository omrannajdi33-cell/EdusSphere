<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\ActivityHomeworkRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'skill_id' => [
                'required',
                Rule::exists('skills', 'id')->where(fn ($q) => $q->where('subject_id', $this->input('subject_id'))),
            ],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            ...ActivityHomeworkRules::rules($this),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_homework' => $this->boolean('is_homework'),
            'require_result_photo' => $this->boolean('require_result_photo'),
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Donne un titre à ton activité.',
            'subject_id.required' => 'Choisis une matière.',
            'skill_id.required' => 'Choisis une compétence.',
            'skill_id.exists' => 'Cette compétence ne correspond pas à la matière choisie.',
            ...ActivityHomeworkRules::messages(),
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        return ActivityHomeworkRules::normalize(parent::validated());
    }
}
