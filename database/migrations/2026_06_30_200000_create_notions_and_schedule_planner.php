<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notion_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['subject_id', 'display_order']);
        });

        Schema::create('notions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notion_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['notion_category_id', 'display_order']);
        });

        Schema::create('activity_notion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['activity_id', 'notion_id']);
        });

        Schema::create('exam_notion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'notion_id']);
        });

        Schema::create('project_notion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'notion_id']);
        });

        Schema::create('schedule_notion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'notion_id']);
        });

        Schema::create('project_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'project_id']);
        });

        Schema::create('schedule_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'student_id']);
        });

        Schema::create('schedule_student_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['activity', 'exam', 'project']);
            $table->unsignedBigInteger('item_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'student_id', 'item_type', 'item_id'], 'schedule_student_item_unique');
            $table->index(['schedule_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_student_items');
        Schema::dropIfExists('schedule_student');
        Schema::dropIfExists('project_schedule');
        Schema::dropIfExists('schedule_notion');
        Schema::dropIfExists('project_notion');
        Schema::dropIfExists('exam_notion');
        Schema::dropIfExists('activity_notion');
        Schema::dropIfExists('notions');
        Schema::dropIfExists('notion_categories');
    }
};
