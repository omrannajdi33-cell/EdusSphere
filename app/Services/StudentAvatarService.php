<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentAvatarService
{
    public function store(Student $student, UploadedFile $file): string
    {
        $this->deleteFile($student->avatar_path);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::uuid().'.'.$ext;

        $path = $file->storeAs('avatars/'.$student->id, $filename, 'local');
        $student->update(['avatar_path' => $path]);

        return $path;
    }

    public function deleteFor(Student $student): void
    {
        $this->deleteFile($student->avatar_path);
        $student->update(['avatar_path' => null]);
    }

    public function response(Student $student): BinaryFileResponse
    {
        if (! $student->avatar_path || ! Storage::disk('local')->exists($student->avatar_path)) {
            abort(404);
        }

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($student->avatar_path) ?: $this->guessMimeType($student->avatar_path);

        return response()->file($disk->path($student->avatar_path), [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="avatar"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    protected function guessMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
