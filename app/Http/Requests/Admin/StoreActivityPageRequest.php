<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    public function rules(): array
    {
        $types = array_keys(config('activity.page_types', []));

        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($types)],
            'body' => ['nullable', 'string'],
            'passage' => ['nullable', 'string', 'max:50000'],
            'pdf' => ['nullable', 'file', 'max:15360', 'mimes:pdf'],
            'audio' => ['nullable', 'file', 'max:20480', 'mimes:mp3,wav,ogg,m4a,mpeg'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Donne un titre à cette étape.',
            'type.required' => 'Choisis un format d\'étape (PDF, écriture ou interactif).',
            'type.in' => 'Format d\'étape invalide.',
            'pdf.required' => 'Un fichier PDF est requis pour une feuille PDF.',
            'pdf.mimes' => 'Seuls les fichiers PDF sont acceptés.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('type') === 'pdf_worksheet' && ! $this->hasFile('pdf')) {
                $validator->errors()->add('pdf', 'Téléverse un PDF pour cette étape.');
            }
            if (in_array($this->input('type'), ['reading_comprehension', 'recitation'], true) && ! filled($this->input('passage'))) {
                $validator->errors()->add('passage', 'Ajoute le texte à lire pour cette étape.');
            }
        });
    }
}
