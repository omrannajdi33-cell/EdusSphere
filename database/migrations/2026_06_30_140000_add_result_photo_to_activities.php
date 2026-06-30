<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('require_result_photo')->default(false)->after('is_homework');
        });

        Schema::table('progressions', function (Blueprint $table) {
            $table->string('result_photo_path')->nullable()->after('time_spent_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('require_result_photo');
        });

        Schema::table('progressions', function (Blueprint $table) {
            $table->dropColumn('result_photo_path');
        });
    }
};
