<?php

namespace App\Support;

use Carbon\Carbon;

class DailyDiscovery
{
    public static function today(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $facts = config('daily_facts', []);
        $index = ($date->dayOfYear() - 1) % max(count($facts), 1);

        return array_merge($facts[$index] ?? self::fallback(), [
            'day_of_year' => $date->dayOfYear(),
            'date_label' => $date->translatedFormat('l j F'),
            'streak_key' => 'edusphere_streak_'.$date->format('Y'),
        ]);
    }

    public static function fallback(): array
    {
        return [
            'category' => 'Science',
            'emoji' => '🔭',
            'color' => '#6366f1',
            'title' => 'La curiosité est un super-pouvoir',
            'fact' => 'Poser des questions et explorer chaque jour rend ton cerveau plus fort — comme un muscle !',
            'question' => 'Quelle découverte scientifique t\'intrigue le plus ?',
        ];
    }
}
