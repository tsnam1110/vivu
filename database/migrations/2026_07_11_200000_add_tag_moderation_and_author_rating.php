<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('usage_count')->index();
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        // Existing seed/data are admin-curated → approved
        DB::table('tags')->update(['status' => 'approved']);

        Schema::table('experiences', function (Blueprint $table) {
            $table->unsignedTinyInteger('author_rating')->nullable()->after('google_place_id');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn('author_rating');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('status');
        });
    }
};
