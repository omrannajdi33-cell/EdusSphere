<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_action_id')->constrained()->cascadeOnDelete();
            $table->foreignId('awarded_by')->constrained('users')->cascadeOnDelete();
            $table->integer('value');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};
