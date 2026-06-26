<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'target_type' => ['required', Rule::in(['all', 'level', 'student'])],
            'target_id' => [
                Rule::requiredIf(fn () => in_array($this->input('target_type'), ['level', 'student'], true)),
                'nullable',
                'integer',
            ],
            'publish_now' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'body.required' => 'Le message est obligatoire.',
            'target_id.required' => 'Choisis un destinataire.',
        ];
    }
}
