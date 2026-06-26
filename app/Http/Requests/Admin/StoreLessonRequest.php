<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'skill_id' => ['required', 'exists:skills,id'],
            'school_level_id' => ['nullable', 'exists:school_levels,id'],
            'estimated_duration_min' => ['nullable', 'integer', 'min:5', 'max:480'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'subject_id.required' => 'Choisis une matière.',
            'skill_id.required' => 'Choisis une compétence.',
        ];
    }
}
