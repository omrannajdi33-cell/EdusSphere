<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\MediaFile;
use App\Services\LessonDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class LessonDocumentController extends Controller
{
    public function store(Request $request, Lesson $lesson, LessonDocumentService $documents): RedirectResponse
    {
        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'max:51200', 'mimes:pdf,ppt,pptx'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:160'],
        ]);

        $items = [];
        /** @var array<int, UploadedFile> $files */
        $files = $data['documents'];
        $labels = $data['labels'] ?? [];

        foreach ($files as $index => $file) {
            $items[] = [
                'file' => $file,
                'label' => $labels[$index] ?? null,
            ];
        }

        $uploaded = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            try {
                $documents->store($lesson, $item['file'], $item['label']);
                $uploaded++;
            } catch (\RuntimeException $e) {
                $name = $item['label'] ?: $item['file']->getClientOriginalName();
                $errors[] = $name.': '.$e->getMessage();
            }
        }

        if ($uploaded === 0) {
            return back()->withErrors(['documents' => implode(' ', $errors) ?: 'Aucun document n’a pu être ajouté.']);
        }

        $message = $uploaded === 1
            ? 'Document ajouté à la leçon.'
            : $uploaded.' documents ajoutés à la leçon.';

        if ($errors !== []) {
            return back()
                ->with('success', $message)
                ->withErrors(['documents' => implode(' · ', $errors)]);
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, Lesson $lesson, MediaFile $media): RedirectResponse
    {
        abort_unless($media->lesson_id === $lesson->id, 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:160'],
        ]);

        $media->update(['label' => trim($data['label'])]);

        return back()->with('success', 'Nom du document mis à jour.');
    }

    public function destroy(Lesson $lesson, MediaFile $media, LessonDocumentService $documents): RedirectResponse
    {
        abort_unless($media->lesson_id === $lesson->id, 404);

        $documents->delete($media);

        return back()->with('success', 'Document supprimé.');
    }
}
