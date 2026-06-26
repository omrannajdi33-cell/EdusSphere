<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = ['subject_id', 'name', 'weight_percent', 'display_order'];

    protected function casts(): array
    {
        return [
            'weight_percent' => 'decimal:2',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public static function subjectTotalWeight(int $subjectId, ?int $excludeId = null): float
    {
        $query = static::where('subject_id', $subjectId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return (float) $query->sum('weight_percent');
    }

    public static function isValidSubjectTotal(int $subjectId, float $additionalWeight = 0, ?int $excludeId = null): bool
    {
        $total = static::subjectTotalWeight($subjectId, $excludeId) + $additionalWeight;

        return abs($total - 100.0) < 0.01;
    }

    public static function wouldExceedMax(int $subjectId, float $weight, ?int $excludeId = null): bool
    {
        return static::subjectTotalWeight($subjectId, $excludeId) + $weight > 100.01;
    }
}
