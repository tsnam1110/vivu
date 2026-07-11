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
            $table->string('dish_role', 32)->nullable()->after('supports_cook_home');
            $table->string('thermal_nature', 16)->nullable()->after('five_element');
            $table->string('protein_source', 16)->nullable()->after('thermal_nature');
            $table->string('cooking_method', 16)->nullable()->after('protein_source');
            $table->json('flavor_tags')->nullable()->after('cooking_method');
            $table->json('facts_meta')->nullable()->after('search_keywords')
                ->comment('Provenance / verification metadata per field');

            $table->index('dish_role');
            $table->index('thermal_nature');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropIndex(['dish_role']);
            $table->dropIndex(['thermal_nature']);
            $table->dropColumn([
                'dish_role',
                'thermal_nature',
                'protein_source',
                'cooking_method',
                'flavor_tags',
                'facts_meta',
            ]);
        });
    }
};
