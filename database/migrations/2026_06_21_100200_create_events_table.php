<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['exam', 'homework', 'reminder', 'event']);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
