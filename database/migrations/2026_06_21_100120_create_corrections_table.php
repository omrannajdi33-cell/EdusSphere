<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['submitted', 'to_correct', 'corrected', 'returned', 'validated'])->default('submitted');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrections');
    }
};
