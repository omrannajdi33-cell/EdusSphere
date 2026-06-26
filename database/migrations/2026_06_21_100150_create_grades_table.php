<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('value', 5, 2);
            $table->enum('type', ['activity', 'exam', 'average_skill', 'average_subject', 'general']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
