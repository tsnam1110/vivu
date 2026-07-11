<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->json('culinary_regions')
                ->nullable()
                ->after('dish_role')
                ->comment('region_tags: bac|trung|nam|tay_nguyen|quoc_gia|hoa_viet|ngoai');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('culinary_regions');
        });
    }
};
