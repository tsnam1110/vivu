<?php

namespace Database\Seeders;

use App\Enums\TagStatus;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'an' => ['Món Hàn', 'Món Nhật', 'Hải sản', 'Chay', 'Món Việt', 'Nướng', 'Lẩu', 'Buffet'],
            'uong' => ['Trà sữa', 'Sinh tố', 'Rượu', 'Trà', 'Nước ép'],
            'ca-phe' => ['View đẹp', 'Yên tĩnh', 'Sống ảo', 'Làm việc', 'Rooftop'],
            'du-lich' => ['Biển', 'Núi', 'Văn hóa', 'Check-in', 'Trekking'],
            'luu-tru' => ['Homestay', 'Resort', 'Gần trung tâm', 'View đẹp', 'Giá tốt'],
        ];

        $global = ['Gia đình', 'Cặp đôi', 'Giá rẻ', 'Sang chảnh', 'Bạn bè', 'Solo', 'Pet-friendly'];

        foreach ($map as $categorySlug => $names) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }
            foreach ($names as $name) {
                Tag::query()->firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => \Illuminate\Support\Str::slug($name),
                    ],
                    [
                        'name' => $name,
                        'status' => TagStatus::Approved,
                    ],
                );
            }
        }

        foreach ($global as $name) {
            Tag::query()->firstOrCreate(
                [
                    'category_id' => null,
                    'slug' => \Illuminate\Support\Str::slug($name),
                ],
                [
                    'name' => $name,
                    'status' => TagStatus::Approved,
                ],
            );
        }
    }
}
