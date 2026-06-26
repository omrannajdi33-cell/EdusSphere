<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('subjects.official', []) as $order => $data) {
            Subject::updateOrCreate(
                ['name' => $data['name']],
                [
                    'color' => $data['color'],
                    'icon' => $data['icon'],
                    'display_order' => $order + 1,
                ],
            );
        }
    }
}
