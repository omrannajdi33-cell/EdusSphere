<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointReward extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cost',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cost' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('cost')->orderBy('name');
    }
}
