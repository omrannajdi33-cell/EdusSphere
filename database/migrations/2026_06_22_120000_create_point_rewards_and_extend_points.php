<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('description')->nullable();
            $table->unsignedInteger('cost');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_reward_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cost');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_redemptions');
        Schema::dropIfExists('point_rewards');
    }
};
