<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\SchoolLevel;
use App\Models\Student;
use Illuminate\Database\Seeder;

class SchoolLevelSeeder extends Seeder
{
    /** Nomenclature québécoise — 1re à 6e année du primaire. */
    private const LEVELS = [
        'Primaire 1',
        'Primaire 2',
        'Primaire 3',
        'Primaire 4',
        'Primaire 5',
        'Primaire 6',
    ];

    /** Anciens libellés France → équivalent Québec (migration des données existantes). */
    private const LEGACY_MAP = [
        'CP' => 'Primaire 1',
        'CE1' => 'Primaire 2',
        'CE2' => 'Primaire 3',
        'CM1' => 'Primaire 4',
        'CM2' => 'Primaire 5',
    ];

    public function run(): void
    {
        $this->migrateLegacyLevels();

        foreach (self::LEVELS as $order => $name) {
            SchoolLevel::updateOrCreate(
                ['name' => $name],
                ['display_order' => $order + 1],
            );
        }

        SchoolLevel::query()
            ->whereNotIn('name', self::LEVELS)
            ->whereIn('name', array_keys(self::LEGACY_MAP))
            ->delete();
    }

    private function migrateLegacyLevels(): void
    {
        foreach (self::LEGACY_MAP as $oldName => $newName) {
            $old = SchoolLevel::where('name', $oldName)->first();
            $new = SchoolLevel::where('name', $newName)->first();

            if (! $old) {
                continue;
            }

            if (! $new) {
                $old->update(['name' => $newName]);

                continue;
            }

            Student::where('school_level_id', $old->id)->update(['school_level_id' => $new->id]);
            Lesson::where('school_level_id', $old->id)->update(['school_level_id' => $new->id]);
            $old->delete();
        }
    }
}
