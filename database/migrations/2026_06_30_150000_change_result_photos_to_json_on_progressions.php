<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progressions', function (Blueprint $table) {
            $table->json('result_photos')->nullable()->after('time_spent_seconds');
        });

        if (Schema::hasColumn('progressions', 'result_photo_path')) {
            DB::table('progressions')
                ->whereNotNull('result_photo_path')
                ->orderBy('id')
                ->each(function (object $row): void {
                    DB::table('progressions')
                        ->where('id', $row->id)
                        ->update(['result_photos' => json_encode([$row->result_photo_path])]);
                });

            Schema::table('progressions', function (Blueprint $table) {
                $table->dropColumn('result_photo_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('progressions', function (Blueprint $table) {
            $table->string('result_photo_path')->nullable()->after('time_spent_seconds');
        });

        DB::table('progressions')
            ->whereNotNull('result_photos')
            ->orderBy('id')
            ->each(function (object $row): void {
                $photos = json_decode($row->result_photos, true);
                $first = is_array($photos) ? ($photos[0] ?? null) : null;

                DB::table('progressions')
                    ->where('id', $row->id)
                    ->update(['result_photo_path' => $first]);
            });

        Schema::table('progressions', function (Blueprint $table) {
            $table->dropColumn('result_photos');
        });
    }
};
