<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
