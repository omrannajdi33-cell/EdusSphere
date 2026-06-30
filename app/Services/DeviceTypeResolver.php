<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Exam;
use App\Models\Project;

class DeviceTypeResolver
{
    public const TABLET = 'tablet';

    public const COMPUTER = 'computer';

    /** @return list<string> */
    public function tabletPageTypes(): array
    {
        return config('edusphere.tablet_page_types', []);
    }

    public function pageTypeRequiresTablet(string $type): bool
    {
        return in_array($type, $this->tabletPageTypes(), true);
    }

    /** @param  list<string>  $pageTypes */
    public function resolveFromPageTypes(array $pageTypes): string
    {
        foreach ($pageTypes as $type) {
            if ($this->pageTypeRequiresTablet($type)) {
                return self::TABLET;
            }
        }

        return self::COMPUTER;
    }

    public function forActivity(Activity $activity): string
    {
        $types = $activity->relationLoaded('pages')
            ? $activity->pages->pluck('type')->all()
            : $activity->pages()->pluck('type')->all();

        return $this->resolveFromPageTypes($types);
    }

    public function forExam(Exam $exam): string
    {
        $types = $exam->relationLoaded('pages')
            ? $exam->pages->pluck('type')->all()
            : $exam->pages()->pluck('type')->all();

        return $this->resolveFromPageTypes($types);
    }

    public function forProject(Project $project): string
    {
        return match ($project->project_type) {
            'creative' => self::TABLET,
            default => self::COMPUTER,
        };
    }

    /**
     * @param  iterable<int, object{device_type?: string|null}|array{device_type?: string|null}>  $items
     * @return array{tablet: int, computer: int}
     */
    public function countForItems(iterable $items): array
    {
        $counts = ['tablet' => 0, 'computer' => 0];

        foreach ($items as $item) {
            $type = is_array($item)
                ? ($item['device_type'] ?? self::COMPUTER)
                : ($item->device_type ?? self::COMPUTER);

            if ($type === self::TABLET) {
                $counts['tablet']++;
            } else {
                $counts['computer']++;
            }
        }

        return $counts;
    }

    /**
     * @param  array{tablet: int, computer: int}  $counts
     */
    public function formatScheduleSummary(array $counts): string
    {
        $parts = [];

        if ($counts['tablet'] > 0) {
            $parts[] = '📱 '.$counts['tablet'];
        }

        if ($counts['computer'] > 0) {
            $parts[] = '💻 '.$counts['computer'];
        }

        return implode(' · ', $parts);
    }
}
