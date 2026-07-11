<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('emoji', 16)->nullable();
            $table->string('summary', 500)->nullable();
            $table->json('meal_slots');
            $table->boolean('supports_light')->default(false);
            $table->boolean('supports_main')->default(true);
            $table->boolean('supports_dine_out')->default(true);
            $table->boolean('supports_cook_home')->default(true);
            $table->string('five_element', 16)->nullable();
            $table->unsignedSmallInteger('calories_kcal')->nullable();
            $table->unsignedSmallInteger('cook_minutes')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('steps')->nullable();
            $table->text('benefits')->nullable();
            $table->text('harms')->nullable();
            $table->text('advice')->nullable();
            $table->text('notes')->nullable();
            $table->string('search_keywords', 255)->nullable();
            $table->string('status', 20)->default('published');
            $table->string('source', 20)->default('system');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('suggest_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('five_element');
            $table->index(['supports_light', 'supports_main']);
            $table->index(['supports_dine_out', 'supports_cook_home']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
