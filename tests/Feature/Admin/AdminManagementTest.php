<?php

namespace Tests\Feature\Admin;

use App\Models\Skill;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, SkillSeeder::class]);
        $this->teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    }

    public function test_teacher_can_view_dashboard_with_stats(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord');
    }

    public function test_teacher_can_create_student(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.students.store'), [
                'first_name' => 'Nadia',
                'last_name' => 'Test',
                'email' => 'nadia@test.fr',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'nadia@test.fr', 'role' => User::ROLE_STUDENT]);
        $this->assertDatabaseHas('students', ['first_name' => 'Nadia']);
    }

    public function test_teacher_can_create_student_with_short_password(): void
    {
        $this->actingAs($this->teacher)
            ->post(route('admin.students.store'), [
                'first_name' => 'Karim',
                'last_name' => 'Test',
                'email' => 'karim@test.fr',
                'password' => '123',
                'password_confirmation' => '123',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'karim@test.fr']);
    }

    public function test_duplicate_student_email_shows_french_error(): void
    {
        User::factory()->create(['email' => 'dup@test.fr']);

        $this->actingAs($this->teacher)
            ->from(route('admin.students.create'))
            ->post(route('admin.students.store'), [
                'first_name' => 'Test',
                'last_name' => 'Dup',
                'email' => 'dup@test.fr',
                'password' => '123',
                'password_confirmation' => '123',
                'status' => 'active',
            ])
            ->assertInvalid(['email' => 'Cet email est déjà utilisé.']);
    }

    public function test_teacher_can_manage_subjects(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.subjects.index'))
            ->assertOk()
            ->assertSee('Français');

        $this->actingAs($this->teacher)
            ->post(route('admin.subjects.store'), [
                'name' => 'Informatique',
                'color' => '#6366f1',
                'icon' => 'calculator',
                'display_order' => 99,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', ['name' => 'Informatique']);
    }

    public function test_skill_weight_cannot_exceed_one_hundred_percent(): void
    {
        $subject = Subject::where('name', 'Éducation physique')->firstOrFail();

        $this->actingAs($this->teacher)
            ->post(route('admin.subjects.skills.store', $subject), [
                'name' => 'Compétence en trop',
                'weight_percent' => 10,
            ])
            ->assertSessionHasErrors('weight_percent');
    }

    public function test_student_cannot_access_admin_crud(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }
}
