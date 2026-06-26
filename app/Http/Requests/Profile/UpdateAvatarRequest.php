<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        $mimes = config('edusphere.avatar.mimes', ['jpg', 'jpeg', 'png', 'webp']);
        $maxKb = config('edusphere.avatar.max_kb', 5120);

        return [
            'avatar' => [
                'required',
                'file',
                'max:'.$maxKb,
                'mimes:'.implode(',', $mimes),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Choisis une photo.',
            'avatar.max' => 'La photo ne doit pas dépasser 5 Mo.',
            'avatar.mimes' => 'Formats acceptés : JPG, PNG, WEBP.',
        ];
    }
}
