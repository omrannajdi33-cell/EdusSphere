<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'subject_id',
        'skill_id',
        'lesson_id',
        'title',
        'description',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ActivityPage::class)->orderBy('page_order');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function assignedStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'activity_student')->withTimestamps();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function publishTo(array $studentIds): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);

        $this->assignedStudents()->sync($studentIds);
    }

    public function isVisibleToStudent(?Student $student): bool
    {
        if (! $this->isPublished() || ! $student) {
            return false;
        }

        return $this->assignedStudents()
            ->where('student_id', $student->id)
            ->exists();
    }

    public function publish(): void
    {
        $this->update(['status' => 'published', 'published_at' => now()]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft', 'published_at' => null]);
    }
}
