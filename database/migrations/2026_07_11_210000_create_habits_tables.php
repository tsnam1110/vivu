<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 120);
            $table->string('description', 500)->nullable();
            $table->string('icon', 16)->nullable();
            $table->string('color', 32)->default('teal');
            $table->enum('frequency', ['daily', 'weekly'])->default('daily');
            $table->unsignedTinyInteger('target_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'archived_at']);
            $table->index(['user_id', 'sort_order']);
            $table->index('category_id');
        });

        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('logged_on');
            $table->string('note', 255)->nullable();
            $table->foreignId('experience_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['habit_id', 'logged_on']);
            $table->index(['user_id', 'logged_on']);
            $table->index(['habit_id', 'logged_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
        Schema::dropIfExists('habits');
    }
};
