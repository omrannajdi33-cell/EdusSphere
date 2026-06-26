<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_periods', function (Blueprint $table) {
            $table->unsignedTinyInteger('sort_order')->default(1)->after('school_year');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('report_period_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            $table->json('payload')->nullable()->after('subject_averages');
        });

        if (Schema::hasTable('report_periods')) {
            DB::table('report_periods')->orderBy('id')->get()->values()->each(function ($period, $index) {
                DB::table('report_periods')->where('id', $period->id)->update(['sort_order' => $index + 1]);
            });

            $year = now()->format('Y').'-'.(now()->year + 1);
            if (DB::table('report_periods')->count() === 1) {
                DB::table('report_periods')->insert([
                    [
                        'label' => 'Trimestre 2',
                        'school_year' => $year,
                        'sort_order' => 2,
                        'starts_at' => now()->startOfYear()->addMonths(4)->toDateString(),
                        'ends_at' => now()->startOfYear()->addMonths(8)->toDateString(),
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'label' => 'Trimestre 3',
                        'school_year' => $year,
                        'sort_order' => 3,
                        'starts_at' => now()->startOfYear()->addMonths(8)->toDateString(),
                        'ends_at' => now()->endOfYear()->toDateString(),
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('report_period_id');
            $table->dropColumn('payload');
        });

        Schema::table('report_periods', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
