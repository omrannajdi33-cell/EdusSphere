<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Correction extends Model
{
    protected $fillable = [
        'student_id',
        'activity_id',
        'exam_attempt_id',
        'project_submission_id',
        'teacher_id',
        'status',
        'score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function projectSubmission(): BelongsTo
    {
        return $this->belongsTo(ProjectSubmission::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(CorrectionHistory::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class);
    }
}
