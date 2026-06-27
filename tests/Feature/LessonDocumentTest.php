<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Skill;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        Storage::fake('private');
    }

    private function makeLesson(): Lesson
    {
        $subject = Subject::where('name', 'Français')->firstOrFail();
        $skill = Skill::where('subject_id', $subject->id)->firstOrFail();

        return Lesson::create([
            'subject_id' => $subject->id,
            'skill_id' => $skill->id,
            'title' => 'Leçon PDF',
            'status' => 'draft',
        ]);
    }

    public function test_teacher_can_upload_pdf_to_lesson(): void
    {
        $lesson = $this->makeLesson();
        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.documents.store', $lesson), [
                'documents' => [$file],
                'labels' => ['Mon cours'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('media_files', [
            'lesson_id' => $lesson->id,
            'source_kind' => 'pdf',
            'label' => 'Mon cours',
        ]);
    }

    public function test_teacher_can_upload_multiple_documents(): void
    {
        $lesson = $this->makeLesson();

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.documents.store', $lesson), [
                'documents' => [
                    UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'),
                    UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'),
                ],
                'labels' => ['Partie A', 'Partie B'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, $lesson->mediaFiles()->count());
    }

    public function test_teacher_can_upload_pptx_without_conversion(): void
    {
        $lesson = $this->makeLesson();
        $file = UploadedFile::fake()->create(
            'cours.pptx',
            100,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.documents.store', $lesson), [
                'documents' => [$file],
                'labels' => ['Présentation'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $media = $lesson->mediaFiles()->firstOrFail();

        $this->assertSame('pptx', $media->source_kind);
        $this->assertSame($media->path, $media->display_path);
        $this->assertStringEndsWith('.pptx', $media->path);
    }

    public function test_teacher_can_rename_document(): void
    {
        $lesson = $this->makeLesson();
        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.documents.store', $lesson), ['documents' => [$file]]);

        $media = $lesson->mediaFiles()->firstOrFail();

        $this->actingAs($this->teacher)
            ->put(route('admin.lessons.documents.update', [$lesson, $media]), ['label' => 'Support révisé'])
            ->assertRedirect();

        $this->assertSame('Support révisé', $media->fresh()->label);
    }

    public function test_student_lesson_page_loads_document_viewer_assets(): void
    {
        $lesson = $this->makeLesson();
        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $this->actingAs($this->teacher)
            ->post(route('admin.lessons.documents.store', $lesson), ['documents' => [$file]]);

        $lesson->update(['status' => 'published', 'published_at' => now()]);

        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Lina',
            'last_name' => 'Test',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.lessons.show', $lesson))
            ->assertOk()
            ->assertSee('data-document-viewer', false)
            ->assertSee('document-viewer', false);
    }
}
