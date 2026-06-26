<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
    protected $fillable = [
        'name',
        'school_level_id',
    ];

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $level = $this->schoolLevel?->name;

        return $level ? "{$this->name} · {$level}" : $this->name;
    }
}
