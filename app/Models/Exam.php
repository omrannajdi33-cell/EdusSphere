<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'subject_id',
        'skill_id',
        'report_period_id',
        'weight_percent',
        'source_activity_id',
        'title',
        'description',
        'duration_minutes',
        'max_attempts',
        'opens_at',
        'closes_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'weight_percent' => 'decimal:2',
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

    public function sourceActivity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'source_activity_id');
    }

    public function reportPeriod(): BelongsTo
    {
        return $this->belongsTo(ReportPeriod::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ExamPage::class)->orderBy('page_order');
    }

    public function hasOwnContent(): bool
    {
        return $this->pages()->exists();
    }

    public function contentReady(): bool
    {
        return $this->hasOwnContent() || $this->source_activity_id;
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isOpenNow(): bool
    {
        if ($this->status === 'draft' || $this->status === 'closed') {
            return false;
        }

        return now()->between($this->opens_at, $this->closes_at);
    }

    public function isUpcoming(): bool
    {
        return $this->opens_at->isFuture() && $this->status !== 'draft';
    }

    public function isFinished(): bool
    {
        return $this->closes_at->isPast() || $this->status === 'closed';
    }

    public function studentAttemptCount(int $studentId): int
    {
        return $this->attempts()->where('student_id', $studentId)->count();
    }

    public function canStudentStart(int $studentId): bool
    {
        if (! $this->isOpenNow() || ! $this->contentReady()) {
            return false;
        }

        $inProgress = $this->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->exists();

        if ($inProgress) {
            return true;
        }

        return $this->studentAttemptCount($studentId) < $this->max_attempts;
    }
}
