<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_frame', 32)->default('none')->after('avatar_path');
            $table->boolean('has_premium_avatar')->default(false)->after('avatar_frame');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_frame', 'has_premium_avatar']);
        });
    }
};
