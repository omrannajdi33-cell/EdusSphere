<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annotation extends Model
{
    protected $fillable = ['correction_id', 'activity_page_id', 'teacher_id', 'data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function correction(): BelongsTo
    {
        return $this->belongsTo(Correction::class);
    }

    public function activityPage(): BelongsTo
    {
        return $this->belongsTo(ActivityPage::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
