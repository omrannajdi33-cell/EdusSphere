<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'avatar_path',
        'school_level_id',
        'class_group_id',
        'ui_preferences',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'ui_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_student')->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
