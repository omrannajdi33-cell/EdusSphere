<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->after('skill_id')->constrained()->nullOnDelete();
        });

        Schema::table('media_files', function (Blueprint $table) {
            $table->string('display_path')->nullable()->after('path');
            $table->string('source_kind', 20)->nullable()->after('mime_type');
        });

        Schema::create('lesson_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
            $table->json('content');
            $table->timestamps();

            $table->unique(['student_id', 'media_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_annotations');

        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn(['display_path', 'source_kind']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lesson_id');
        });
    }
};
