<?php

namespace Database\Seeders;

use App\Models\PointAction;
use Illuminate\Database\Seeder;

class PointActionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = [
            ['name' => 'Participation', 'description' => 'Participation active en classe', 'value' => 1, 'type' => 'positive'],
            ['name' => 'Excellent travail', 'description' => 'Travail de grande qualité', 'value' => 2, 'type' => 'positive'],
            ['name' => 'Respect', 'description' => 'Respect des règles et des autres', 'value' => 1, 'type' => 'positive'],
            ['name' => 'Entraide', 'description' => 'Aide un camarade', 'value' => 2, 'type' => 'positive'],
            ['name' => 'Persévérance', 'description' => 'Effort soutenu malgré les difficultés', 'value' => 2, 'type' => 'positive'],
            ['name' => 'Distraction', 'description' => 'Perturbation ou manque d\'attention', 'value' => -1, 'type' => 'negative'],
            ['name' => 'Retard', 'description' => 'Arrivée en retard', 'value' => -1, 'type' => 'negative'],
            ['name' => 'Manque d\'effort', 'description' => 'Peu ou pas d\'effort fourni', 'value' => -2, 'type' => 'negative'],
        ];

        foreach ($actions as $action) {
            PointAction::updateOrCreate(
                ['name' => $action['name']],
                $action + ['is_active' => true],
            );
        }
    }
}
