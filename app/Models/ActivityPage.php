<?php

namespace App\Models;

use App\Models\Concerns\HasPageTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityPage extends Model
{
    use HasPageTypes;

    protected $fillable = ['activity_id', 'page_order', 'title', 'type', 'content'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('display_order');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function mediaFile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MediaFile::class);
    }

    public function audioMediaFile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MediaFile::class)->where('mime_type', 'like', 'audio/%');
    }

    public function audioFile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MediaFile::class)->where('mime_type', 'like', 'audio/%');
    }

    public function passageText(): ?string
    {
        return $this->content['passage'] ?? null;
    }

    public function isRtl(): bool
    {
        return (bool) ($this->content['rtl'] ?? $this->type === 'recitation');
    }
}
