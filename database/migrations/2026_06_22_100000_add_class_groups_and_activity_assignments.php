<?php

use App\Models\Activity;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('school_level_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_level_id', 'name']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('class_group_id')
                ->nullable()
                ->after('school_level_id')
                ->constrained('class_groups')
                ->nullOnDelete();
        });

        Schema::create('activity_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['activity_id', 'student_id']);
        });

        $studentIds = Student::query()
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->pluck('id');

        if ($studentIds->isNotEmpty()) {
            $now = now();
            $rows = Activity::query()
                ->where('status', 'published')
                ->pluck('id')
                ->flatMap(fn ($activityId) => $studentIds->map(fn ($studentId) => [
                    'activity_id' => $activityId,
                    'student_id' => $studentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]))
                ->all();

            if ($rows !== []) {
                DB::table('activity_student')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_student');

        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_group_id');
        });

        Schema::dropIfExists('class_groups');
    }
};
