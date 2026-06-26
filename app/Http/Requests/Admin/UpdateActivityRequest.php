<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'skill_id.exists' => 'Cette compétence ne correspond pas à la matière choisie.',
        ];
    }
}
