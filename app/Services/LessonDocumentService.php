<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class LessonDocumentService
{
    /** @return list<string> */
    public static function allowedExtensions(): array
    {
        return ['pdf', 'ppt', 'pptx'];
    }

    public function store(Lesson $lesson, UploadedFile $file, ?string $label = null): MediaFile
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        abort_unless(in_array($ext, self::allowedExtensions(), true), 422, 'Format non supporté.');

        $storedName = Str::uuid().'.'.$ext;
        $directory = 'lessons/'.$lesson->id.'/documents';
        $path = $file->storeAs($directory, $storedName, 'local');

        $displayLabel = trim((string) $label) !== '' ? trim((string) $label) : $file->getClientOriginalName();

        return MediaFile::create([
            'lesson_id' => $lesson->id,
            'filename' => $file->getClientOriginalName(),
            'label' => $displayLabel,
            'path' => $path,
            'display_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'source_kind' => $ext,
            'size_bytes' => $file->getSize(),
            'page_count' => $this->estimatePageCount($path, $ext),
        ]);
    }

    /** @param  list<array{file: UploadedFile, label: ?string}>  $items */
    public function storeMany(Lesson $lesson, array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $this->store($lesson, $item['file'], $item['label'] ?? null);
            $count++;
        }

        return $count;
    }

    public function delete(MediaFile $media): void
    {
        if ($media->path && Storage::disk('local')->exists($media->path)) {
            Storage::disk('local')->delete($media->path);
        }

        if ($media->display_path && $media->display_path !== $media->path && Storage::disk('local')->exists($media->display_path)) {
            Storage::disk('local')->delete($media->display_path);
        }

        $media->delete();
    }

    public function viewerPath(MediaFile $media): string
    {
        return $media->display_path ?: $media->path;
    }

    public function displayName(MediaFile $media): string
    {
        return $media->label ?: $media->filename;
    }

    public function viewerMimeType(MediaFile $media): string
    {
        return match ($media->source_kind) {
            'pdf' => 'application/pdf',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt' => 'application/vnd.ms-powerpoint',
            default => $media->mime_type ?: 'application/octet-stream',
        };
    }

    protected function estimatePageCount(string $path, string $ext): ?int
    {
        return match ($ext) {
            'pdf' => $this->estimatePdfPages($path),
            'pptx' => $this->estimatePptxSlides($path),
            default => null,
        };
    }

    protected function estimatePdfPages(string $pdfPath): ?int
    {
        if (! Storage::disk('local')->exists($pdfPath)) {
            return null;
        }

        $content = Storage::disk('local')->get($pdfPath);
        if (! is_string($content)) {
            return null;
        }

        $count = preg_match_all('/\/Type\s*\/Page[^s]/', $content);

        return $count > 0 ? $count : null;
    }

    protected function estimatePptxSlides(string $pptxPath): ?int
    {
        if (! Storage::disk('local')->exists($pptxPath)) {
            return null;
        }

        $zip = new ZipArchive;
        if ($zip->open(Storage::disk('local')->path($pptxPath)) !== true) {
            return null;
        }

        $count = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name) && preg_match('#^ppt/slides/slide\d+\.xml$#', $name)) {
                $count++;
            }
        }
        $zip->close();

        return $count > 0 ? $count : null;
    }
}
