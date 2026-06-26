<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'student_id',
        'report_period_id',
        'period_label',
        'general_average',
        'subject_averages',
        'payload',
        'comments',
        'pdf_path',
        'generated_by',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'general_average' => 'decimal:2',
            'subject_averages' => 'array',
            'payload' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reportPeriod(): BelongsTo
    {
        return $this->belongsTo(ReportPeriod::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
