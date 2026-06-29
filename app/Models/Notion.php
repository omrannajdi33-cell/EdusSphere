<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notion extends Model
{
    protected $fillable = [
        'notion_category_id',
        'subject_id',
        'title',
        'content',
        'display_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NotionCategory::class, 'notion_category_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->withTimestamps();
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class)->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_notion')->withTimestamps();
    }

    public function excerpt(int $length = 120): string
    {
        $text = trim(strip_tags($this->content));

        return mb_strlen($text) > $length
            ? mb_substr($text, 0, $length).'…'
            : $text;
    }
}
