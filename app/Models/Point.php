<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    public $timestamps = false;

    protected $fillable = ['student_id', 'point_action_id', 'awarded_by', 'value', 'note', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function pointAction(): BelongsTo
    {
        return $this->belongsTo(PointAction::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
