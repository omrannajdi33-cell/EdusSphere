<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DailyDiscovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeDiscoveryTest extends TestCase
{
    use RefreshDatabase;
    public function test_homepage_shows_daily_discovery(): void
    {
        $discovery = DailyDiscovery::today();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Découverte #'.$discovery['day_of_year'], false)
            ->assertSee(str_replace("'", '&#039;', e($discovery['title'])), false);
    }

    public function test_student_sees_homepage_not_redirect(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Découverte', false);
    }

    public function test_teacher_is_redirected_from_home(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
