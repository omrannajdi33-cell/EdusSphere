<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('correction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_history');
    }
};
