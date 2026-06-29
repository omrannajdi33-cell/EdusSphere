<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('device_type', 20)->nullable()->after('description');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->string('device_type', 20)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('device_type');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('device_type');
        });
    }
};
