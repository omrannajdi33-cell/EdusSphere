<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_publish_announcement_for_all(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->post(route('admin.announcements.store'), [
                'title' => 'Réunion parents',
                'body' => 'Mercredi à 18h',
                'target_type' => 'all',
                'publish_now' => true,
            ])
            ->assertRedirect();

        $announcement = Announcement::first();
        $this->assertNotNull($announcement->published_at);
    }

    public function test_student_dashboard_shows_announcement(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        Announcement::create([
            'title' => 'Info importante',
            'body' => 'N oublie pas ton matériel',
            'target_type' => 'all',
            'published_at' => now(),
            'created_by' => $teacher->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Info importante');
    }
}
