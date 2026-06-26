<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight_percent', 5, 2)->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
