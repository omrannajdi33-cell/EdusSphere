<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    protected $fillable = [
        'subject_id',
        'school_level_id',
        'title',
        'color',
        'day_of_week',
        'period_number',
        'starts_at',
        'ends_at',
        'uses_custom_time',
        'schedule_date',
        'materials',
        'plan',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'uses_custom_time' => 'boolean',
        ];
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->withTimestamps();
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class)->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_schedule')->withTimestamps();
    }

    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class, 'schedule_notion')->withTimestamps();
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function isVisibleToStudent(?Student $student): bool
    {
        if (! $student?->school_level_id || ! $this->school_level_id) {
            return false;
        }

        return (int) $this->school_level_id === (int) $student->school_level_id;
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

    public function hasPlanningDetails(): bool
    {
        if (filled($this->materials) || filled($this->plan) || $this->uses_custom_time) {
            return true;
        }

        if ($this->relationLoaded('activities') && $this->activities->isNotEmpty()) {
            return true;
        }

        if ($this->relationLoaded('exams') && $this->exams->isNotEmpty()) {
            return true;
        }

        if ($this->relationLoaded('projects') && $this->projects->isNotEmpty()) {
            return true;
        }

        if ($this->relationLoaded('notions') && $this->notions->isNotEmpty()) {
            return true;
        }

        return false;
    }

    public function timeLabel(): string
    {
        return substr((string) $this->starts_at, 0, 5).'–'.substr((string) $this->ends_at, 0, 5);
    }

    public static function defaultTimesForPeriod(int $periodNumber): array
    {
        $period = config('schedule.periods.'.$periodNumber, []);

        return [
            'starts_at' => $period['starts_at'] ?? '08:30',
            'ends_at' => $period['ends_at'] ?? '09:45',
        ];
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
