<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_pages', function (Blueprint $table) {
            $table->string('type', 30)->default('interactive')->after('activity_id');
        });

        Schema::table('media_files', function (Blueprint $table) {
            $table->foreignId('activity_page_id')->nullable()->after('activity_id')->constrained()->nullOnDelete();
        });

        Schema::table('progressions', function (Blueprint $table) {
            $table->string('workflow_status', 20)->default('in_progress')->after('percent_complete');
            $table->timestamp('submitted_at')->nullable()->after('workflow_status');
        });
    }

    public function down(): void
    {
        Schema::table('progressions', function (Blueprint $table) {
            $table->dropColumn(['workflow_status', 'submitted_at']);
        });

        Schema::table('media_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_page_id');
        });

        Schema::table('activity_pages', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
