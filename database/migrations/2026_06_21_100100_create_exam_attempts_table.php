<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('pages_visited')->default(0);
            $table->unsignedInteger('answers_count')->default(0);
            $table->unsignedInteger('attempts_remaining')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'corrected'])->default('in_progress');
            $table->timestamps();

            $table->index(['student_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
