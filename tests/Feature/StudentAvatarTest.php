<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_student_avatar(): void
    {
        Storage::fake('local');

        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $student = $this->makeStudent();

        $path = UploadedFile::fake()->create('photo.jpg', 20, 'image/jpeg')->store('avatars/'.$student->id, 'local');
        $student->update(['avatar_path' => $path]);

        $this->actingAs($teacher)
            ->get(route('admin.students.avatar.show', $student, absolute: false))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename="avatar"');
    }

    public function test_student_can_view_own_avatar(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $user->id,
            'first_name' => 'Lina',
            'last_name' => 'Test',
        ]);

        $path = UploadedFile::fake()->create('photo.jpg', 20, 'image/jpeg')->store('avatars/'.$student->id, 'local');
        $student->update(['avatar_path' => $path]);

        $this->actingAs($user)
            ->get(route('student.profile.avatar.show', absolute: false))
            ->assertOk();
    }

    public function test_avatar_url_is_relative_with_cache_buster(): void
    {
        $student = Student::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_STUDENT])->id,
            'first_name' => 'A',
            'last_name' => 'B',
            'avatar_path' => 'avatars/1/test.jpg',
        ]);

        $url = $student->avatarUrl('admin');

        $this->assertStringStartsWith('/admin/students/', $url);
        $this->assertStringContainsString('?v=', $url);
    }

    private function makeStudent(): Student
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        return Student::create([
            'user_id' => $user->id,
            'first_name' => 'Samir',
            'last_name' => 'Test',
        ]);
    }
}
