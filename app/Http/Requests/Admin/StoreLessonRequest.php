<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
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
            'school_level_id' => ['nullable', 'exists:school_levels,id'],
            'estimated_duration_min' => ['nullable', 'integer', 'min:5', 'max:480'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'external_links' => ['nullable', 'array', 'max:10'],
            'external_links.*.label' => ['nullable', 'string', 'max:160'],
            'external_links.*.url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $links = collect((array) $this->input('external_links', []))
            ->map(fn ($link) => [
                'label' => trim((string) ($link['label'] ?? '')),
                'url' => trim((string) ($link['url'] ?? '')),
            ])
            ->filter(fn (array $link) => $link['label'] !== '' || $link['url'] !== '')
            ->values()
            ->all();

        $this->merge(['external_links' => $links === [] ? null : $links]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'subject_id.required' => 'Choisis une matière.',
            'skill_id.required' => 'Choisis une compétence.',
            'external_links.*.url.url' => 'Chaque lien doit être une adresse web valide (https://…).',
        ];
    }
}
