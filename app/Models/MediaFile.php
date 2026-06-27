<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    protected $fillable = [
        'lesson_id',
        'activity_id',
        'activity_page_id',
        'filename',
        'label',
        'path',
        'display_path',
        'mime_type',
        'source_kind',
        'size_bytes',
        'page_count',
    ];

    public function displayName(): string
    {
        return $this->label ?: $this->filename;
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityPage(): BelongsTo
    {
        return $this->belongsTo(ActivityPage::class);
    }

    public function storagePath(): ?string
    {
        return $this->display_path ?: $this->path;
    }

    public function fileExists(): bool
    {
        return \App\Support\PrivateStorage::exists($this->storagePath());
    }
}
