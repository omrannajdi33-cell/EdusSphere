<?php

namespace Database\Seeders;

use App\Models\PointReward;
use Illuminate\Database\Seeder;

class PointRewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            ['name' => 'Autocollant', 'description' => 'Un autocollant au choix', 'cost' => 5, 'display_order' => 1],
            ['name' => '10 min de jeu libre', 'description' => 'Temps de jeu en classe', 'cost' => 10, 'display_order' => 2],
            ['name' => 'Privilège du matin', 'description' => 'Premier choix de place ou d\'activité', 'cost' => 15, 'display_order' => 3],
            ['name' => 'Bonbon / surprise', 'description' => 'Petite récompense du prof', 'cost' => 20, 'display_order' => 4],
        ];

        foreach ($rewards as $reward) {
            PointReward::updateOrCreate(
                ['name' => $reward['name']],
                $reward + ['is_active' => true],
            );
        }
    }
}
