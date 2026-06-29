<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->string('project_type', 32)->default('research');
            $table->string('submission_format', 16)->default('both');
            $table->boolean('require_sources')->default(true);
            $table->boolean('require_bibliography')->default(true);
            $table->timestamp('due_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
        });

        Schema::create('project_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'student_id']);
        });

        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('workflow_status', ['in_progress', 'submitted', 'returned', 'corrected'])->default('in_progress');
            $table->longText('content')->nullable();
            $table->json('sources')->nullable();
            $table->json('bibliography')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'student_id']);
            $table->index(['workflow_status', 'submitted_at']);
        });

        Schema::create('project_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_submission_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('label')->nullable();
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });

        Schema::table('media_files', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('activity_page_id')->constrained()->nullOnDelete();
        });

        Schema::table('corrections', function (Blueprint $table) {
            $table->foreignId('project_submission_id')->nullable()->after('exam_attempt_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('corrections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_submission_id');
        });

        Schema::table('media_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('project_submission_files');
        Schema::dropIfExists('project_submissions');
        Schema::dropIfExists('project_student');
        Schema::dropIfExists('projects');
    }
};
