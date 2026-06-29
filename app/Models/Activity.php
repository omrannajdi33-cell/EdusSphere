<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    public const HOMEWORK_DURING_SCHOOL = 'during_school';

    public const HOMEWORK_AFTER_SCHOOL = 'after_school';

    protected $fillable = [
        'subject_id',
        'skill_id',
        'lesson_id',
        'title',
        'description',
        'is_homework',
        'due_at',
        'homework_slot',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_homework' => 'boolean',
            'due_at' => 'datetime',
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

    public function isHomework(): bool
    {
        return (bool) $this->is_homework;
    }

    public function homeworkSlotLabel(): ?string
    {
        if (! $this->isHomework() || ! $this->homework_slot) {
            return null;
        }

        return config('activity.homework_slots.'.$this->homework_slot);
    }

    public function isPendingForStudent(?Student $student): bool
    {
        if (! $student) {
            return false;
        }

        $progression = Progression::query()
            ->where('student_id', $student->id)
            ->where('activity_id', $this->id)
            ->first();

        return ! $progression
            || ! in_array($progression->workflow_status, ['submitted', 'corrected'], true);
    }

    public function isOverdueForStudent(?Student $student): bool
    {
        if (! $this->isHomework() || ! $this->due_at || ! $student) {
            return false;
        }

        if (! $this->isPendingForStudent($student)) {
            return false;
        }

        return now()->isAfter($this->due_at);
    }

    /** @param  Builder<Activity>  $query */
    public function scopeHomework(Builder $query): Builder
    {
        return $query->where('is_homework', true);
    }

    /** @param  Builder<Activity>  $query */
    public function scopeNotHomework(Builder $query): Builder
    {
        return $query->where('is_homework', false);
    }

    /** @param  Builder<Activity>  $query */
    public function scopeAssignedToStudent(Builder $query, Student $student): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereHas('assignedStudents', fn ($q) => $q->where('students.id', $student->id));
    }
}
