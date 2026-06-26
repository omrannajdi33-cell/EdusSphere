<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAnnotation extends Model
{
    protected $fillable = ['student_id', 'lesson_id', 'media_file_id', 'content'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}
