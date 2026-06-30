<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progression extends Model
{
    protected $fillable = [
        'student_id',
        'lesson_id',
        'activity_id',
        'last_page',
        'percent_complete',
        'workflow_status',
        'submitted_at',
        'time_spent_seconds',
        'result_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'percent_complete' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
