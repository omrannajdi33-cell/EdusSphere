<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('color', 7)->nullable();
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period_number');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->date('schedule_date')->nullable();
            $table->timestamps();

            $table->index(['day_of_week', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
