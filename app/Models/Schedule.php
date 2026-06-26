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
}
