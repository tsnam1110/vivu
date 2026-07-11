<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->unsignedSmallInteger('serving_grams')
                ->nullable()
                ->after('calories_kcal')
                ->comment('Khối lượng (g) tương ứng calories_kcal');
        });

        // Backfill: khẩu phần chuẩn theo loại món (ước lượng)
        $rows = DB::table('dishes')
            ->whereNotNull('calories_kcal')
            ->whereNull('serving_grams')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $slug = (string) $row->slug;
            $grams = match (true) {
                str_contains($slug, 'ca-phe') => 250,
                str_contains($slug, 'sinh-to') => 300,
                str_contains($slug, 'sua-chua'), str_contains($slug, 'trai-cay'), str_contains($slug, 'che-') => 150,
                str_contains($slug, 'salad'), str_contains($slug, 'goi-cuon'), str_contains($slug, 'sup-') => 200,
                (bool) $row->supports_light && ! $row->supports_main => 120,
                default => 350,
            };

            DB::table('dishes')->where('id', $row->id)->update(['serving_grams' => $grams]);
        }
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('serving_grams');
        });
    }
};
