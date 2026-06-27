<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointRedemption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'point_reward_id',
        'cost',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function pointReward(): BelongsTo
    {
        return $this->belongsTo(PointReward::class);
    }
}
