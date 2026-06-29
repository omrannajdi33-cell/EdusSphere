<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'student_id',
        'workflow_status',
        'content',
        'research_notes',
        'sources',
        'bibliography',
        'submitted_at',
        'last_saved_at',
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'bibliography' => 'array',
            'submitted_at' => 'datetime',
            'last_saved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectSubmissionFile::class);
    }

    public function correction(): HasOne
    {
        return $this->hasOne(Correction::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->workflow_status, ['submitted', 'corrected'], true);
    }

    public function canEdit(): bool
    {
        return in_array($this->workflow_status, ['in_progress', 'returned'], true);
    }

    public function statusLabel(): string
    {
        return config('project.workflow_statuses.'.$this->workflow_status, $this->workflow_status);
    }
}
