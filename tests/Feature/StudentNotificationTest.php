<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_notifications_center(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'activity_returned',
            'data' => [
                'activity_title' => 'Rédaction',
                'comment' => 'Refais la fin.',
                'url' => '/student/activities/1/play',
            ],
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('student.notifications.index'))
            ->assertOk()
            ->assertSee('renvoyée')
            ->assertSee('Refais la fin.');
    }
}
