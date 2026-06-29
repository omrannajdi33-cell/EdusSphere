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
            'device_type' => ['required', Rule::in(array_keys(config('edusphere.device_types', [])))],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            ...ActivityHomeworkRules::rules($this),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_homework' => $this->boolean('is_homework'),
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Donne un titre à ton activité.',
            'subject_id.required' => 'Choisis une matière.',
            'skill_id.required' => 'Choisis une compétence.',
            'skill_id.exists' => 'Cette compétence ne correspond pas à la matière choisie.',
            'device_type.required' => 'Indique si l\'activité se fait sur tablette ou ordinateur.',
            ...ActivityHomeworkRules::messages(),
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        return ActivityHomeworkRules::normalize(parent::validated());
    }
}
