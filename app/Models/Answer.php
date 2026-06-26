<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'student_id',
        'question_id',
        'exam_question_id',
        'activity_page_id',
        'exam_page_id',
        'exam_attempt_id',
        'content',
        'is_correct',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_correct' => 'boolean',
            'score' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function activityPage(): BelongsTo
    {
        return $this->belongsTo(ActivityPage::class);
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }
}
