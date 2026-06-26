<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_order')->default(1);
            $table->string('title')->nullable();
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'page_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pages');
    }
};
