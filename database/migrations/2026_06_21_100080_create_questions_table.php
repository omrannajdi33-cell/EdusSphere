<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->text('prompt');
            $table->json('config')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('activity_page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
