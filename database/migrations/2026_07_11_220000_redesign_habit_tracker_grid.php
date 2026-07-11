<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('habit_logs');
        Schema::dropIfExists('habits');

        Schema::create('habit_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('description', 500)->nullable();
            $table->string('icon', 16)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('habit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habit_item_id')->constrained('habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            // done = ✓, missed = ✗ ; no row = empty/null
            $table->enum('status', ['done', 'missed']);
            $table->timestamps();

            $table->unique(['user_id', 'habit_item_id', 'entry_date'], 'habit_entries_user_item_date_unique');
            $table->index(['user_id', 'entry_date']);
            $table->index(['habit_item_id', 'entry_date']);
        });

        Schema::create('habit_entry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habit_item_id')->constrained('habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('from_status', 16)->nullable(); // null | done | missed
            $table->string('to_status', 16)->nullable();
            $table->string('source', 32)->default('web');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['user_id', 'changed_at']);
            $table->index(['user_id', 'habit_item_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_entry_histories');
        Schema::dropIfExists('habit_entries');
        Schema::dropIfExists('habit_items');

        // Restore previous v1 tables shape if rolled back (minimal).
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
        });
    }
};
