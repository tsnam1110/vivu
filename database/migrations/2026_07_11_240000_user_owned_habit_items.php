<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rebuild entries/histories against per-user items (admin catalog = templates only).
        Schema::dropIfExists('habit_entry_histories');
        Schema::dropIfExists('habit_entries');

        Schema::create('user_habit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Optional link to admin template (null = fully custom text).
            $table->foreignId('template_habit_item_id')
                ->nullable()
                ->constrained('habit_items')
                ->nullOnDelete();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->string('icon', 16)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active', 'sort_order']);
            // One personal row per template per user (custom items have null template).
            $table->unique(['user_id', 'template_habit_item_id'], 'uhi_user_template_uq');
        });

        Schema::create('habit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_habit_item_id')->constrained('user_habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            $table->enum('status', ['done', 'missed']);
            $table->timestamps();

            $table->unique(['user_id', 'user_habit_item_id', 'entry_date'], 'he_user_item_date_uq');
            $table->index(['user_id', 'entry_date'], 'he_user_date_idx');
            $table->index(['user_habit_item_id', 'entry_date'], 'he_item_date_idx');
        });

        Schema::create('habit_entry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_habit_item_id')->constrained('user_habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('from_status', 16)->nullable();
            $table->string('to_status', 16)->nullable();
            $table->string('source', 32)->default('web');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['user_id', 'changed_at'], 'heh_user_changed_idx');
            $table->index(['user_id', 'user_habit_item_id', 'entry_date'], 'heh_user_item_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_entry_histories');
        Schema::dropIfExists('habit_entries');
        Schema::dropIfExists('user_habit_items');

        Schema::create('habit_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habit_item_id')->constrained('habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            $table->enum('status', ['done', 'missed']);
            $table->timestamps();
            $table->unique(['user_id', 'habit_item_id', 'entry_date'], 'habit_entries_user_item_date_unique');
        });

        Schema::create('habit_entry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habit_item_id')->constrained('habit_items')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('from_status', 16)->nullable();
            $table->string('to_status', 16)->nullable();
            $table->string('source', 32)->default('web');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }
};
