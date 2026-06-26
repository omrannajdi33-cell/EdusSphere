<?php

namespace App\Support;

use App\Models\Subject;

class SubjectWorkspace
{
    /** @return array{label: string, recommended: list<string>, hint: string}|null */
    public static function forSubject(?Subject $subject): ?array
    {
        if (! $subject) {
            return null;
        }

        $slug = $subject->slug;

        return config("subject_workspaces.{$slug}");
    }

    /** @return list<string> */
    public static function recommendedTypes(?Subject $subject): array
    {
        return self::forSubject($subject)['recommended'] ?? [];
    }

    /** @return array<string, array<string, mixed>> */
    public static function pageTypesForSubject(?Subject $subject): array
    {
        $all = config('activity.page_types', []);
        $recommended = self::recommendedTypes($subject);

        if ($recommended === []) {
            return $all;
        }

        $ordered = [];
        foreach ($recommended as $key) {
            if (isset($all[$key])) {
                $ordered[$key] = $all[$key] + ['featured' => true];
            }
        }

        foreach ($all as $key => $meta) {
            if (! isset($ordered[$key])) {
                $ordered[$key] = $meta + ['featured' => false];
            }
        }

        return $ordered;
    }
}
