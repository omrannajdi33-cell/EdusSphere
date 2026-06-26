<?php

namespace App\Support;

use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectTheme
{
    public static function all(): array
    {
        if (Subject::query()->exists()) {
            return Subject::ordered()->get()->map->theme()->all();
        }

        return config('subjects.official', []);
    }

    public static function find(string $slug): ?array
    {
        if (Subject::query()->exists()) {
            $subject = Subject::ordered()->get()->first(fn (Subject $s) => $s->slug === $slug);

            return $subject?->theme();
        }

        foreach (config('subjects.official', []) as $subject) {
            if ($subject['slug'] === $slug) {
                return $subject;
            }
        }

        return null;
    }

    public static function findByName(string $name): ?array
    {
        $subject = Subject::where('name', $name)->first();

        return $subject?->theme();
    }

    public static function iconPath(string $icon): string
    {
        return config("subjects.icons.{$icon}", config('subjects.icons.book-open'));
    }

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }
}
