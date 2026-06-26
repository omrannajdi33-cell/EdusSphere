<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('max_attempts')->default(1);
            $table->timestamp('opens_at');
            $table->timestamp('closes_at');
            $table->enum('status', ['draft', 'scheduled', 'open', 'closed'])->default('draft');
            $table->timestamps();

            $table->index(['subject_id', 'status']);
            $table->index(['opens_at', 'closes_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
