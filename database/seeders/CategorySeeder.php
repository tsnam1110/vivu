<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Ăn', 'slug' => 'an', 'icon' => '🍜', 'description' => 'Quán ăn, nhà hàng', 'sort_order' => 1],
            ['name' => 'Uống', 'slug' => 'uong', 'icon' => '🍹', 'description' => 'Quán nước, bar', 'sort_order' => 2],
            ['name' => 'Cà phê', 'slug' => 'ca-phe', 'icon' => '☕', 'description' => 'Quán cà phê', 'sort_order' => 3],
            ['name' => 'Du lịch', 'slug' => 'du-lich', 'icon' => '✈️', 'description' => 'Điểm du lịch', 'sort_order' => 4],
            ['name' => 'Lưu trú', 'slug' => 'luu-tru', 'icon' => '🏠', 'description' => 'Homestay, khách sạn', 'sort_order' => 5],
        ];

        foreach ($items as $item) {
            Category::query()->updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['is_active' => true]),
            );
        }
    }
}
