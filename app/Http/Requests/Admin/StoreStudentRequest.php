<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $mimes = config('edusphere.avatar.mimes', ['jpg', 'jpeg', 'png', 'webp']);
        $maxKb = config('edusphere.avatar.max_kb', 5120);

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'school_level_id' => ['nullable', 'exists:school_levels,id'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'file', 'max:'.$maxKb, 'mimes:'.implode(',', $mimes)],
        ];
    }
}
