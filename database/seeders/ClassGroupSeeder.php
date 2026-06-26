<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\SchoolLevel;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        $level = SchoolLevel::where('name', 'Primaire 3')->first()
            ?? SchoolLevel::orderBy('display_order')->first();

        if (! $level) {
            return;
        }

        foreach (['Classe A', 'Classe B'] as $name) {
            ClassGroup::updateOrCreate(
                ['school_level_id' => $level->id, 'name' => $name],
                [],
            );
        }
    }
}
