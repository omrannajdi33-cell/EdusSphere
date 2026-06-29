<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'report_period_id' => ['required', 'exists:report_periods,id'],
            'weight_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => [
                'integer',
                Rule::exists('skills', 'id')->where(fn ($q) => $q->where('subject_id', $this->input('subject_id'))),
            ],
            'skill_weights' => ['nullable', 'array'],
            'skill_weights.*' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'project_type' => ['required', Rule::in(array_keys(config('project.project_types', [])))],
            'submission_format' => ['required', Rule::in(array_keys(config('project.submission_formats', [])))],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'require_sources' => ['nullable', 'boolean'],
            'require_bibliography' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $skillIds = array_values(array_unique(array_map('intval', $this->input('skill_ids', []))));
            if ($skillIds === []) {
                return;
            }

            $weights = $this->input('skill_weights', []);
            $provided = collect($skillIds)
                ->map(fn (int $id) => isset($weights[$id]) ? (float) $weights[$id] : null)
                ->filter(fn ($w) => $w !== null);

            if ($provided->isNotEmpty() && $provided->count() !== count($skillIds)) {
                $validator->errors()->add('skill_weights', 'Indique un poids pour chaque compétence sélectionnée, ou laisse vide pour une répartition égale.');

                return;
            }

            if ($provided->isNotEmpty() && abs($provided->sum() - 100) > 0.01) {
                $validator->errors()->add('skill_weights', 'La somme des poids par compétence doit être égale à 100 %.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'require_sources' => $this->boolean('require_sources'),
            'require_bibliography' => $this->boolean('require_bibliography'),
            'skill_ids' => array_values(array_filter((array) $this->input('skill_ids', []))),
        ]);
    }
}
