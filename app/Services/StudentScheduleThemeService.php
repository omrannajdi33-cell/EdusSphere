<?php

namespace App\Services;

use App\Models\Subject;
use App\Support\SubjectTheme;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class StudentScheduleThemeService
{
    public function __construct(
        private ScheduleGrid $grid,
    ) {}

    /** @return array<string, mixed> */
    public function resolve(?CarbonInterface $at = null): array
    {
        $at = Carbon::parse($at ?? now());

        if ($this->isWeekend($at) && ! $this->grid->hasCoursesOn($at)) {
            return $this->buildWeekendTheme();
        }

        $periodNumber = $this->grid->currentPeriodNumber($at);
        if ($periodNumber === null) {
            return $this->buildDefaultTheme();
        }

        $slot = $this->grid->currentSlot($at);
        if ($slot === null) {
            return $this->buildDefaultTheme();
        }

        return $this->buildSubjectTheme($slot, $periodNumber, $at);
    }

    public function currentPeriodNumber(CarbonInterface $at): ?int
    {
        return $this->grid->currentPeriodNumber($at);
    }

    private function isWeekend(CarbonInterface $at): bool
    {
        return $at->dayOfWeekIso >= 6;
    }

    /** @return array<string, mixed> */
    private function buildWeekendTheme(): array
    {
        $base = config('schedule-themes.weekend', []);

        return array_merge($base, [
            'mode' => 'weekend',
            'icon' => 'moon',
            'period_label' => null,
            'current_slot' => null,
        ]);
    }

    /** @return array<string, mixed> */
    private function buildDefaultTheme(): array
    {
        $base = config('schedule-themes.default', []);

        return array_merge($base, [
            'mode' => 'default',
            'name' => null,
            'icon' => null,
            'period_label' => null,
            'current_slot' => null,
        ]);
    }

    /** @param array<string, mixed> $slot */
    private function buildSubjectTheme(array $slot, int $periodNumber, CarbonInterface $at): array
    {
        $subject = isset($slot['subject_id'])
            ? Subject::find($slot['subject_id'])
            : null;

        $slug = $subject?->slug ?? SubjectTheme::slugFromName((string) ($slot['subject'] ?? 'cours'));
        $official = SubjectTheme::find($slug) ?? [];
        $extras = config("schedule-themes.subjects.{$slug}", []);

        $periodDef = config("schedule.periods.{$periodNumber}", []);
        $color = $slot['color'] ?? $official['color'] ?? '#4f46e5';

        return [
            'mode' => 'subject',
            'slug' => $slug,
            'name' => $slot['subject'] ?? $official['name'] ?? $slot['title'],
            'color' => $color,
            'icon' => $official['icon'] ?? 'book-open',
            'greeting' => $extras['greeting'] ?? ('Mode '.($slot['subject'] ?? 'Cours')),
            'tagline' => $extras['tagline'] ?? ($slot['title'] ?? null),
            'emoji' => $extras['emoji'] ?? '📘',
            'deco' => $extras['deco'] ?? [],
            'gradient' => $this->gradientForColor($color),
            'period_label' => $periodDef['label'] ?? "Période {$periodNumber}",
            'period_number' => $periodNumber,
            'current_slot' => $slot,
            'ends_at' => $slot['ends_at'] ?? $periodDef['ends_at'] ?? null,
        ];
    }

    /** @return list<string> */
    private function gradientForColor(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return ['#eef2ff', '#e0f2fe', '#f8fafc'];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $light = sprintf('rgb(%d %d %d / 0.14)', min(255, $r + 40), min(255, $g + 40), min(255, $b + 40));
        $mid = sprintf('rgb(%d %d %d / 0.08)', $r, $g, $b);

        return [$light, $mid, '#f8fafc'];
    }
}
