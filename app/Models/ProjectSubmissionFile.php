<?php

namespace App\Models;

use App\Support\PrivateStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmissionFile extends Model
{
    protected $fillable = [
        'project_submission_id',
        'filename',
        'label',
        'path',
        'mime_type',
        'size_bytes',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ProjectSubmission::class, 'project_submission_id');
    }

    public function displayName(): string
    {
        return $this->label ?: $this->filename;
    }

    public function fileExists(): bool
    {
        return PrivateStorage::exists($this->path);
    }
}
