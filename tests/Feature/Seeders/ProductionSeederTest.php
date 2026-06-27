<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Database\Seeders\PointActionSeeder;
use Database\Seeders\ProductionAdminSeeder;
use Database\Seeders\SchoolLevelSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_stack_creates_admin_without_demo_users(): void
    {
        $this->seed([
            SchoolLevelSeeder::class,
            PointActionSeeder::class,
            SubjectSeeder::class,
            SkillSeeder::class,
            ProductionAdminSeeder::class,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@ontech.com',
            'role' => User::ROLE_TEACHER,
        ]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subjects', 8);
    }
}
