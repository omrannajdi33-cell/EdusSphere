<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\SchoolLevel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $level = SchoolLevel::where('name', 'Primaire 3')->first()
            ?? SchoolLevel::first();

        $teacher = User::updateOrCreate(
            ['email' => 'prof@edusphere.fr'],
            [
                'name' => 'Professeur Demo',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
            ],
        );

        $classA = ClassGroup::where('name', 'Classe A')->where('school_level_id', $level?->id)->first();
        $classB = ClassGroup::where('name', 'Classe B')->where('school_level_id', $level?->id)->first();

        $students = [
            ['email' => 'eleve1@edusphere.fr', 'first_name' => 'Amina', 'last_name' => 'Benali', 'class_group_id' => $classA?->id],
            ['email' => 'eleve2@edusphere.fr', 'first_name' => 'Youssef', 'last_name' => 'Karim', 'class_group_id' => $classA?->id],
            ['email' => 'eleve3@edusphere.fr', 'first_name' => 'Lina', 'last_name' => 'Saadi', 'class_group_id' => $classB?->id],
        ];

        foreach ($students as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'].' '.$data['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STUDENT,
                    'status' => 'active',
                ],
            );

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'school_level_id' => $level?->id,
                    'class_group_id' => $data['class_group_id'] ?? null,
                ],
            );
        }

        unset($teacher);
    }
}
