<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->decimal('weight_kg', 5, 2)->nullable()->after('location_city');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('weight_kg');
            $table->string('gender', 20)->nullable()->after('height_cm');
            $table->unsignedSmallInteger('birth_year')->nullable()->after('gender');
            $table->string('activity_level', 20)->nullable()->after('birth_year');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'weight_kg',
                'height_cm',
                'gender',
                'birth_year',
                'activity_level',
            ]);
        });
    }
};
