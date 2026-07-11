<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taste_traits', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('name', 80);
            $table->string('slug', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taste_traits');
    }
};
