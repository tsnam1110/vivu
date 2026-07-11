<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_avatars')) {
            Schema::create('sample_avatars', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 60)->unique();
                $table->string('name', 100);
                $table->string('path', 255);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('avatar_frames')) {
            Schema::create('avatar_frames', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 60)->unique();
                $table->string('name', 100);
                $table->string('description', 255)->nullable();
                $table->string('effect_type', 32);
                $table->json('effect_config')->nullable();
                $table->boolean('is_premium')->default(false);
                $table->boolean('show_badge')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
                $table->index('is_premium');
            });
        }

        if (! Schema::hasTable('premium_subscriptions')) {
            Schema::create('premium_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                // dateTime (not timestamp) — supports lifetime far-future dates beyond year 2038
                $table->dateTime('starts_at');
                $table->dateTime('ends_at')->nullable();
                $table->string('status', 20)->default('active');
                $table->string('source', 20)->default('admin');
                $table->string('notes', 500)->nullable();
                $table->foreignId('granted_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('ends_at');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sample_avatar_id')) {
                $table->foreignId('sample_avatar_id')->nullable()->after('avatar_path')
                    ->constrained('sample_avatars')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'avatar_frame_id')) {
                $table->foreignId('avatar_frame_id')->nullable()->after('sample_avatar_id')
                    ->constrained('avatar_frames')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'premium_expires_at')) {
                $table->dateTime('premium_expires_at')->nullable()->after('avatar_frame_id');
                $table->index('premium_expires_at');
            }
        });

        // Convert legacy TIMESTAMP column to DATETIME if already created as timestamp (MySQL only)
        if (Schema::hasColumn('users', 'premium_expires_at') && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY premium_expires_at DATETIME NULL');
        }

        // Migrate legacy columns if present
        if (Schema::hasColumn('users', 'has_premium_avatar')) {
            DB::table('users')
                ->where('has_premium_avatar', true)
                ->whereNull('premium_expires_at')
                ->update(['premium_expires_at' => now()->addYears(20)->format('Y-m-d H:i:s')]);
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_frame')) {
                $table->dropColumn('avatar_frame');
            }
            if (Schema::hasColumn('users', 'has_premium_avatar')) {
                $table->dropColumn('has_premium_avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sample_avatar_id')) {
                $table->dropConstrainedForeignId('sample_avatar_id');
            }
            if (Schema::hasColumn('users', 'avatar_frame_id')) {
                $table->dropConstrainedForeignId('avatar_frame_id');
            }
            if (Schema::hasColumn('users', 'premium_expires_at')) {
                $table->dropColumn('premium_expires_at');
            }
            if (! Schema::hasColumn('users', 'avatar_frame')) {
                $table->string('avatar_frame', 32)->default('none')->after('avatar_path');
            }
            if (! Schema::hasColumn('users', 'has_premium_avatar')) {
                $table->boolean('has_premium_avatar')->default(false)->after('avatar_frame');
            }
        });

        Schema::dropIfExists('premium_subscriptions');
        Schema::dropIfExists('avatar_frames');
        Schema::dropIfExists('sample_avatars');
    }
};
