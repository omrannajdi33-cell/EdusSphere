<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleStudentItem extends Model
{
    protected $fillable = [
        'schedule_id',
        'student_id',
        'item_type',
        'item_id',
        'sort_order',
        'notes',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function resolveItem(): Activity|Exam|Project|Lesson|null
    {
        return match ($this->item_type) {
            'activity' => Activity::find($this->item_id),
            'exam' => Exam::find($this->item_id),
            'project' => Project::find($this->item_id),
            'lesson' => Lesson::find($this->item_id),
            default => null,
        };
    }

    /** @return array{id: int, type: string, title: string, notes: string|null, sort_order: int, url: string|null} */
    public function toDisplayArray(): array
    {
        $item = $this->resolveItem();
        $title = match ($this->item_type) {
            'activity' => $item instanceof Activity ? $item->title : 'Activité',
            'exam' => $item instanceof Exam ? $item->title : 'Examen',
            'project' => $item instanceof Project ? $item->title : 'Projet',
            'lesson' => $item instanceof Lesson ? $item->title : 'Leçon',
            default => 'Élément',
        };

        $url = match ($this->item_type) {
            'activity' => $item instanceof Activity ? route('student.activities.play', $item) : null,
            'project' => $item instanceof Project ? route('student.projects.work', $item) : null,
            'lesson' => $item instanceof Lesson ? route('student.lessons.show', $item) : null,
            default => null,
        };

        return [
            'id' => $this->item_id,
            'type' => $this->item_type,
            'title' => $title,
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
            'url' => $url,
        ];
    }
}
