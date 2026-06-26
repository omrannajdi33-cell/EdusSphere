<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointAction extends Model
{
    protected $fillable = ['name', 'description', 'value', 'type', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }
}
