<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE grades MODIFY COLUMN type ENUM('activity', 'exam', 'project', 'average_skill', 'average_subject', 'general') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE grades MODIFY COLUMN type ENUM('activity', 'exam', 'average_skill', 'average_subject', 'general') NOT NULL");
    }
};
