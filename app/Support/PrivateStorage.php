<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class PrivateStorage
{
    public const DISK = 'private';

    public static function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    public static function exists(?string $path): bool
    {
        return filled($path) && self::disk()->exists($path);
    }

    public static function delete(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        return self::disk()->delete($path);
    }
}
