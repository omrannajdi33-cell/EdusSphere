<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolLevel extends Model
{
    protected $fillable = ['name', 'display_order'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class)->orderBy('name');
    }
}
