<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_periods', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->string('school_year', 20)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_order');
            $table->string('title');
            $table->string('type', 30)->default('interactive');
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'page_order']);
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->text('prompt');
            $table->json('config')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->timestamps();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('report_period_id')->nullable()->after('skill_id')->constrained()->nullOnDelete();
            $table->decimal('weight_percent', 5, 2)->default(0)->after('report_period_id');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('exam_page_id')->nullable()->after('activity_page_id')->constrained()->nullOnDelete();
            $table->foreignId('exam_question_id')->nullable()->after('question_id')->constrained()->nullOnDelete();
        });

        if (Schema::hasTable('report_periods') && DB::table('report_periods')->count() === 0) {
            DB::table('report_periods')->insert([
                'label' => 'Trimestre 1',
                'school_year' => now()->format('Y').'-'.(now()->year + 1),
                'starts_at' => now()->startOfYear()->toDateString(),
                'ends_at' => now()->startOfYear()->addMonths(4)->toDateString(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_page_id');
            $table->dropConstrainedForeignId('exam_question_id');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('report_period_id');
            $table->dropColumn('weight_percent');
        });

        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_pages');
        Schema::dropIfExists('report_periods');
    }
};
