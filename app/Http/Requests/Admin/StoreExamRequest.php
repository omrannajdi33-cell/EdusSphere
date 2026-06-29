<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'skill_id' => ['required', 'exists:skills,id'],
            'report_period_id' => ['nullable', 'exists:report_periods,id'],
            'weight_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'source_activity_id' => ['nullable', 'exists:activities,id'],
            'device_type' => ['required', Rule::in(array_keys(config('edusphere.device_types', [])))],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'status' => ['nullable', Rule::in(['draft', 'scheduled', 'open', 'closed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'device_type.required' => 'Indique si l\'examen se fait sur tablette ou ordinateur.',
            'closes_at.after' => 'La date de fermeture doit être après l\'ouverture.',
        ];
    }
}
