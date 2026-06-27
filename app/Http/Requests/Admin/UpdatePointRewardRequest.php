<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePointRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $reward = $this->route('reward');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('point_rewards', 'name')->ignore($reward->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'cost' => ['required', 'integer', 'min:1', 'max:10000'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
