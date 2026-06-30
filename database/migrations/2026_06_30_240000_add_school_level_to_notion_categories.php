<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notion_categories', function (Blueprint $table) {
            $table->foreignId('school_level_id')
                ->nullable()
                ->after('subject_id')
                ->constrained()
                ->nullOnDelete();
        });

        $primaryFiveId = DB::table('school_levels')->where('name', 'Primaire 5')->value('id');

        if ($primaryFiveId) {
            DB::table('notion_categories')
                ->whereNull('school_level_id')
                ->update(['school_level_id' => $primaryFiveId]);
        }
    }

    public function down(): void
    {
        Schema::table('notion_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_level_id');
        });
    }
};
