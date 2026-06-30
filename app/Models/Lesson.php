<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Lesson $lesson): void {
            ScheduleStudentItem::query()
                ->where('item_type', 'lesson')
                ->where('item_id', $lesson->id)
                ->delete();
        });
    }

    protected $fillable = [
        'subject_id',
        'skill_id',
        'title',
        'category',
        'source_ref',
        'description',
        'external_links',
        'cover_image_path',
        'school_level_id',
        'estimated_duration_min',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'external_links' => 'array',
        ];
    }

    /** @return list<array{label: string, url: string}> */
    public function externalLinksForDisplay(): array
    {
        return collect($this->external_links ?? [])
            ->map(fn ($link) => [
                'label' => trim((string) ($link['label'] ?? '')),
                'url' => trim((string) ($link['url'] ?? '')),
            ])
            ->filter(fn (array $link) => $link['url'] !== '')
            ->values()
            ->all();
    }

    public function hasExternalLinks(): bool
    {
        return count($this->externalLinksForDisplay()) > 0;
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LessonAnnotation::class);
    }
}
