<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $displayPath = $ext === 'pdf' ? $path : $this->convertToPdf($path);

        if ($displayPath === null && $ext !== 'pdf') {
            Storage::disk('local')->delete($path);

            throw new \RuntimeException(
                'Impossible de convertir le PowerPoint. Reconstruis l’image Docker (`docker compose build app --no-cache`) pour installer LibreOffice, ou exporte le fichier en PDF.'
            );
        }

        $pageCount = $this->estimatePdfPages($displayPath ?? $path);
        $displayLabel = trim((string) $label) !== '' ? trim((string) $label) : $file->getClientOriginalName();

        return MediaFile::create([
            'lesson_id' => $lesson->id,
            'filename' => $file->getClientOriginalName(),
            'label' => $displayLabel,
            'path' => $path,
            'display_path' => $displayPath ?? $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'source_kind' => $ext,
            'size_bytes' => $file->getSize(),
            'page_count' => $pageCount,
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
        foreach (array_unique(array_filter([$media->path, $media->display_path])) as $filePath) {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
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

    protected function convertToPdf(string $sourcePath): ?string
    {
        $binary = $this->libreOfficeBinary();
        if ($binary === null) {
            Log::warning('LibreOffice introuvable — conversion PowerPoint impossible.');

            return null;
        }

        $disk = Storage::disk('local');
        $fullSource = $disk->path($sourcePath);
        $outputDir = $disk->path(dirname($sourcePath));
        $homeDir = storage_path('app/libreoffice-home');
        $tmpDir = storage_path('app/tmp');

        foreach ([$homeDir, $tmpDir, $outputDir] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        $result = Process::timeout(180)
            ->env([
                'HOME' => $homeDir,
                'TMPDIR' => $tmpDir,
            ])
            ->run([
                $binary,
                '--headless',
                '--nologo',
                '--nofirststartwizard',
                '-env:UserInstallation=file:///'.str_replace('\\', '/', $homeDir.'/profile'),
                '--convert-to',
                'pdf',
                '--outdir',
                $outputDir,
                $fullSource,
            ]);

        if (! $result->successful()) {
            Log::warning('Échec conversion LibreOffice', [
                'exit' => $result->exitCode(),
                'error' => $result->errorOutput(),
                'output' => $result->output(),
            ]);

            return null;
        }

        $pdfName = pathinfo($fullSource, PATHINFO_FILENAME).'.pdf';
        $pdfRelative = dirname($sourcePath).'/'.$pdfName;

        return $disk->exists($pdfRelative) ? $pdfRelative : null;
    }

    protected function libreOfficeBinary(): ?string
    {
        foreach (['/usr/bin/soffice', '/usr/bin/libreoffice'] as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        $which = Process::run(['sh', '-c', 'command -v soffice || command -v libreoffice']);
        if ($which->successful()) {
            $path = trim($which->output());
            if ($path !== '' && is_executable($path)) {
                return $path;
            }
        }

        return null;
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
}
