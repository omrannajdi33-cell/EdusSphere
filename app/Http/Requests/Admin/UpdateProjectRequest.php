<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'skill_id' => [
                'nullable',
                Rule::exists('skills', 'id')->where(fn ($q) => $q->where('subject_id', $this->input('subject_id'))),
            ],
            'project_type' => ['required', Rule::in(array_keys(config('project.project_types', [])))],
            'submission_format' => ['required', Rule::in(array_keys(config('project.submission_formats', [])))],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'require_sources' => ['nullable', 'boolean'],
            'require_bibliography' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'require_sources' => $this->boolean('require_sources'),
            'require_bibliography' => $this->boolean('require_bibliography'),
        ]);
    }
}
