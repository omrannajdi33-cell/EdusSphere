<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'weight_percent' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $subject = $this->route('subject');
            $skill = $this->route('skill');
            $weight = (float) $this->input('weight_percent');

            if (Skill::wouldExceedMax($subject->id, $weight, $skill->id)) {
                $validator->errors()->add(
                    'weight_percent',
                    'Cette pondération dépasserait 100 % pour la matière.'
                );
            }
        });
    }
}
