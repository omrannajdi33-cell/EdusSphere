<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('last_page')->default(1);
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->index('student_id');
            $table->index(['student_id', 'lesson_id']);
            $table->index(['student_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progressions');
    }
};
