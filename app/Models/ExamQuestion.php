<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = ['exam_page_id', 'type', 'prompt', 'config', 'display_order'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function examPage(): BelongsTo
    {
        return $this->belongsTo(ExamPage::class);
    }
}
