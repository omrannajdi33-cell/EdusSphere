<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('birth_date')->nullable();
            $table->string('avatar_path')->nullable();
            $table->foreignId('school_level_id')->nullable()->constrained()->nullOnDelete();
            $table->json('ui_preferences')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('school_level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
