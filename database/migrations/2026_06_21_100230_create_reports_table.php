<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('period_label', 100);
            $table->decimal('general_average', 5, 2);
            $table->json('subject_averages')->nullable();
            $table->text('comments')->nullable();
            $table->string('pdf_path')->nullable();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
