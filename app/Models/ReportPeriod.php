<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportPeriod extends Model
{
    protected $fillable = [
        'label',
        'school_year',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest('id')->first();
    }
}
