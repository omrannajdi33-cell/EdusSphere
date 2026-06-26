<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
