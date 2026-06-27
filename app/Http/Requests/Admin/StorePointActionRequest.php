<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePointActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:point_actions,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['positive', 'negative'])],
            'magnitude' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $magnitude = (int) $data['magnitude'];
        $data['value'] = $data['type'] === 'negative' ? -$magnitude : $magnitude;
        unset($data['magnitude']);

        return $data;
    }
}
