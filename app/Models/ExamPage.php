<?php

namespace App\Models;

use App\Models\Concerns\HasPageTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPage extends Model
{
    use HasPageTypes;

    protected $fillable = ['exam_id', 'page_order', 'title', 'type', 'content'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('display_order');
    }
}
