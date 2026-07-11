<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32);
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->boolean('is_canonical')->default(false);
            $table->string('review_note', 255)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dish_id', 'type', 'status']);
            $table->index('status');
        });

        Schema::create('meal_suggestion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('meal_slot', 20);
            $table->string('meal_size', 20);
            $table->string('meal_mode', 20);
            $table->json('filters_json')->nullable();
            $table->json('suggested_dish_ids');
            $table->foreignId('chosen_dish_id')->nullable()->constrained('dishes')->nullOnDelete();
            $table->string('outcome', 20)->default('suggested');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('user_food_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('diet_flags')->nullable();
            $table->json('disliked_dish_ids')->nullable();
            $table->json('preferred_elements')->nullable();
            $table->string('default_meal_mode', 20)->nullable();
            $table->unsignedSmallInteger('max_calories_default')->nullable();
            $table->boolean('balance_elements')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_food_preferences');
        Schema::dropIfExists('meal_suggestion_logs');
        Schema::dropIfExists('dish_contributions');
    }
};
