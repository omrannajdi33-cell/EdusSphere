<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'color',
        'day_of_week',
        'period_number',
        'starts_at',
        'ends_at',
        'schedule_date',
        'materials',
        'plan',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: ($this->subject?->name ?? 'Cours');
    }

    public function getDisplayColorAttribute(): string
    {
        return $this->color ?: ($this->subject?->color ?? '#4f46e5');
    }

    public function isRecurring(): bool
    {
        return $this->schedule_date === null;
    }

    public function hasNotes(): bool
    {
        return filled($this->materials) || filled($this->plan);
    }

    /** @return list<string> */
    public function materialsLines(): array
    {
        return $this->linesFromText($this->materials);
    }

    /** @return list<string> */
    public function planLines(): array
    {
        return $this->linesFromText($this->plan);
    }

    /** @return list<string> */
    private function linesFromText(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    }

    public function gridLabel(): string
    {
        $subject = $this->subject?->name ?? 'Cours';
        $title = trim($this->title ?? '');

        if ($title === '' || strcasecmp($title, $subject) === 0) {
            return $subject;
        }

        return $title;
    }
}
