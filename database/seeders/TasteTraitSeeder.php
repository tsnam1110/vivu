<?php

namespace Database\Seeders;

use App\Enums\TraitType;
use App\Models\TasteTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TasteTraitSeeder extends Seeder
{
    public function run(): void
    {
        $personality = [
            'Hướng nội' => 'huong-noi',
            'Hướng ngoại' => 'huong-ngoai',
            'Thích phiêu lưu' => 'phieu-luu',
            'Thích yên tĩnh' => 'yen-tinh',
            'Thích khám phá' => 'kham-pha',
            'Thích tiện nghi' => 'tien-nghi',
        ];

        $interests = [
            'Ẩm thực' => 'am-thuc',
            'Nhiếp ảnh' => 'nhiep-anh',
            'Du lịch' => 'du-lich',
            'Cà phê' => 'ca-phe',
            'Biển' => 'bien',
            'Núi rừng' => 'nui-rung',
            'Văn hóa' => 'van-hoa',
            'Thể thao' => 'the-thao',
        ];

        foreach ($personality as $name => $slug) {
            TasteTrait::query()->updateOrCreate(
                ['type' => TraitType::Personality, 'slug' => $slug],
                ['name' => $name, 'is_active' => true],
            );
        }

        foreach ($interests as $name => $slug) {
            TasteTrait::query()->updateOrCreate(
                ['type' => TraitType::Interest, 'slug' => $slug],
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
