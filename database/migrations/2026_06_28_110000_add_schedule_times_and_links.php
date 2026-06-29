<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('uses_custom_time')->default(false)->after('ends_at');
        });

        Schema::create('activity_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'activity_id']);
        });

        Schema::create('exam_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedule');
        Schema::dropIfExists('activity_schedule');

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('uses_custom_time');
        });
    }
};
