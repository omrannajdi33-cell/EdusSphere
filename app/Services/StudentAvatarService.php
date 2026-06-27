<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function response(Student $student): BinaryFileResponse|Response
    {
        if ($student->avatar_path && Storage::disk('local')->exists($student->avatar_path)) {
            $disk = Storage::disk('local');
            $mime = $disk->mimeType($student->avatar_path) ?: $this->guessMimeType($student->avatar_path);

            return response()->file($disk->path($student->avatar_path), [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="avatar"',
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        if ($student->avatar_path) {
            $student->update(['avatar_path' => null]);
        }

        return $this->placeholderResponse($student);
    }

    public function placeholderResponse(Student $student): Response
    {
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($student->full_name ?: '?', 0, 1)), ENT_QUOTES | ENT_XML1);

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200" role="img" aria-label="Avatar">
            <defs>
                <linearGradient id="es-avatar-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#6366f1"/>
                    <stop offset="100%" stop-color="#4f46e5"/>
                </linearGradient>
            </defs>
            <circle cx="100" cy="100" r="100" fill="url(#es-avatar-gradient)"/>
            <text x="100" y="122" text-anchor="middle" fill="#ffffff" font-size="88" font-family="Nunito, Arial, sans-serif" font-weight="800">{$initial}</text>
        </svg>
        SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="avatar.svg"',
            'Cache-Control' => 'private, max-age=300',
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
