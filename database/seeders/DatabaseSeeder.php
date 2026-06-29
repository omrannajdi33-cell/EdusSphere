<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call([
                SchoolLevelSeeder::class,
                PointActionSeeder::class,
                PointRewardSeeder::class,
                SubjectSeeder::class,
                SkillSeeder::class,
                ProductionAdminSeeder::class,
                OfficialNotionsSeeder::class,
            ]);

            return;
        }

        $this->call([
            SchoolLevelSeeder::class,
            ClassGroupSeeder::class,
            PointActionSeeder::class,
            PointRewardSeeder::class,
            SubjectSeeder::class,
            SkillSeeder::class,
            DemoUserSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
