<?php

namespace Tests\Feature;

use App\Models\PointAction;
use App\Models\PointReward;
use App\Models\Student;
use App\Models\User;
use App\Services\BehaviorPointService;
use Database\Seeders\PointActionSeeder;
use Database\Seeders\PointRewardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehaviorPointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PointActionSeeder::class);
        $this->seed(PointRewardSeeder::class);
    }

    public function test_teacher_can_award_points_via_api(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Amina',
            'last_name' => 'Test',
        ]);

        $action = PointAction::where('name', 'Participation')->firstOrFail();

        $response = $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $action->id,
        ]);

        $response->assertOk()->assertJsonPath('total', 1);

        $this->assertDatabaseHas('points', [
            'student_id' => $student->id,
            'point_action_id' => $action->id,
            'value' => 1,
        ]);
    }

    public function test_student_sees_points_page_with_total(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Amina',
            'last_name' => 'Test',
        ]);

        $positive = PointAction::where('name', 'Excellent travail')->firstOrFail();
        $negative = PointAction::where('name', 'Retard')->firstOrFail();

        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $positive->id,
        ]);
        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $negative->id,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.points.index'))
            ->assertOk()
            ->assertSee('Mes points')
            ->assertSee('Excellent travail')
            ->assertSee('Retard');
    }

    public function test_admin_points_page_loads_for_teacher(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('admin.points.index'))
            ->assertOk()
            ->assertSee('Points comportement')
            ->assertSee('Participation');
    }

    public function test_admin_can_manage_point_settings_page(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('admin.points.settings'))
            ->assertOk()
            ->assertSee('Paramètres des points')
            ->assertSee('Autocollant');
    }

    public function test_student_can_redeem_reward_and_teacher_is_notified(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Amina',
            'last_name' => 'Test',
        ]);

        $positive = PointAction::where('name', 'Excellent travail')->firstOrFail();
        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $positive->id,
        ]);
        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $positive->id,
        ]);
        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $positive->id,
        ]);

        $reward = PointReward::where('name', 'Autocollant')->firstOrFail();

        $response = $this->actingAs($studentUser)->postJson(route('student.points.redeem'), [
            'reward_id' => $reward->id,
        ]);

        $response->assertOk()->assertJsonPath('total', 1);

        $this->assertDatabaseHas('point_redemptions', [
            'student_id' => $student->id,
            'point_reward_id' => $reward->id,
            'cost' => 5,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'reward_redeemed',
        ]);
    }

    public function test_student_cannot_redeem_without_enough_points(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Amina',
            'last_name' => 'Test',
        ]);

        $reward = PointReward::where('name', 'Autocollant')->firstOrFail();

        $this->actingAs($studentUser)
            ->postJson(route('student.points.redeem'), ['reward_id' => $reward->id])
            ->assertUnprocessable();
    }

    public function test_total_accounts_for_redemptions(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Amina',
            'last_name' => 'Test',
        ]);

        $positive = PointAction::where('name', 'Participation')->firstOrFail();
        $this->actingAs($teacher)->postJson(route('admin.points.store'), [
            'student_id' => $student->id,
            'point_action_id' => $positive->id,
        ]);

        $reward = PointReward::create([
            'name' => 'Test reward',
            'cost' => 1,
            'is_active' => true,
        ]);

        app(BehaviorPointService::class)->redeem($student, $reward);

        $this->assertSame(0, app(BehaviorPointService::class)->totalFor($student->fresh()));
    }
}
