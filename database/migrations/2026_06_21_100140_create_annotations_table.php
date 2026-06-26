<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
