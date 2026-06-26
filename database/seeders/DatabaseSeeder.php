<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SchoolLevelSeeder::class,
            ClassGroupSeeder::class,
            PointActionSeeder::class,
            SubjectSeeder::class,
            SkillSeeder::class,
            DemoUserSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
