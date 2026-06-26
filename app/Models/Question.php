<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['activity_page_id', 'type', 'prompt', 'config', 'display_order'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function activityPage(): BelongsTo
    {
        return $this->belongsTo(ActivityPage::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
