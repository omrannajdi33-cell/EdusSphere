<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('is_homework')->default(false)->after('description');
            $table->timestamp('due_at')->nullable()->after('is_homework');
            $table->string('homework_slot', 20)->nullable()->after('due_at');

            $table->index(['is_homework', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['is_homework', 'due_at']);
            $table->dropColumn(['is_homework', 'due_at', 'homework_slot']);
        });
    }
};
