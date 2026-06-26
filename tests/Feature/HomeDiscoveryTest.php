<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('login'));
    }

    public function test_student_is_redirected_to_dashboard_from_home(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_teacher_is_redirected_from_home(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
