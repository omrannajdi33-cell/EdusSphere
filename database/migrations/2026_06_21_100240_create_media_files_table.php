<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('page_count')->nullable();
            $table->timestamps();

            $table->index('lesson_id');
            $table->index('activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
