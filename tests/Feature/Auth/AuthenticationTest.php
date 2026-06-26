<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_teacher_can_login_and_access_admin(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_student_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_teacher_cannot_access_student_area(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($user)->get(route('student.dashboard'))->assertForbidden();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
