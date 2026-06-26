<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PublishActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Sélectionne au moins un élève.',
            'student_ids.min' => 'Sélectionne au moins un élève.',
            'student_ids.*.exists' => 'Un élève sélectionné est invalide.',
        ];
    }
}
