<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'subject_id',
        'skill_id',
        'title',
        'description',
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
        ];
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
