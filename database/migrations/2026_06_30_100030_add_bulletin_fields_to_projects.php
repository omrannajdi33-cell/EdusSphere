<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('report_period_id')->nullable()->after('skill_id')->constrained()->nullOnDelete();
            $table->decimal('weight_percent', 5, 2)->default(0)->after('report_period_id');
        });

        Schema::create('project_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_percent', 5, 2)->default(100);
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_skill');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('report_period_id');
            $table->dropColumn('weight_percent');
        });
    }
};
