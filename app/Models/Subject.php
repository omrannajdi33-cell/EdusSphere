<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subject extends Model
{
    protected $fillable = ['name', 'color', 'icon', 'display_order'];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class)->orderBy('display_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function notionCategories(): HasMany
    {
        return $this->hasMany(NotionCategory::class)->orderBy('display_order');
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->name);
    }

    public function theme(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
