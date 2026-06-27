<?php

namespace Tests\Feature;

use App\Models\PointAction;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PointActionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehaviorPointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PointActionSeeder::class);
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
}
