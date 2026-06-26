<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionHistory extends Model
{
    public $timestamps = false;

    protected $table = 'correction_history';

    protected $fillable = ['correction_id', 'user_id', 'action', 'comment', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function correction(): BelongsTo
    {
        return $this->belongsTo(Correction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
