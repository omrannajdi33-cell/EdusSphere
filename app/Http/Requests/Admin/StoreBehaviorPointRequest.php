<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBehaviorPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'point_action_id' => ['required', 'integer', 'exists:point_actions,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
