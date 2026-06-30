<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'subject_id',
        'skill_id',
        'report_period_id',
        'weight_percent',
        'created_by',
        'title',
        'instructions',
        'project_type',
        'submission_format',
        'require_sources',
        'require_bibliography',
        'due_at',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'require_sources' => 'boolean',
            'require_bibliography' => 'boolean',
            'due_at' => 'datetime',
            'published_at' => 'datetime',
            'weight_percent' => 'decimal:2',
        ];
    }

    public function inferredDeviceType(): string
    {
        return app(\App\Services\DeviceTypeResolver::class)->forProject($this);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function reportPeriod(): BelongsTo
    {
        return $this->belongsTo(ReportPeriod::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_skill')
            ->withPivot('weight_percent')
            ->withTimestamps();
    }

    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class)->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function assignedStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'project_student')->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function publishTo(array $studentIds): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);

        $this->assignedStudents()->sync($studentIds);
    }

    public function isVisibleToStudent(?Student $student): bool
    {
        if (! $this->isPublished() || ! $student) {
            return false;
        }

        return $this->assignedStudents()
            ->where('student_id', $student->id)
            ->exists();
    }

    public function typeLabel(): string
    {
        return config('project.project_types.'.$this->project_type.'.label', $this->project_type);
    }

    public function typeIcon(): string
    {
        return config('project.project_types.'.$this->project_type.'.icon', '📁');
    }

    public function formatLabel(): string
    {
        return config('project.submission_formats.'.$this->submission_format.'.label', $this->submission_format);
    }

    public function allowsOnlineWrite(): bool
    {
        return in_array($this->submission_format, ['online', 'both'], true);
    }

    public function allowsUpload(): bool
    {
        return in_array($this->submission_format, ['upload', 'both'], true);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast();
    }

    public function isPendingForStudent(Student $student): bool
    {
        if (! $this->isVisibleToStudent($student)) {
            return false;
        }

        $submission = $this->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (! $submission) {
            return true;
        }

        return in_array($submission->workflow_status, ['in_progress', 'returned'], true);
    }
}
