<?php

namespace Database\Seeders;

use App\Models\HabitItem;
use Illuminate\Database\Seeder;

class HabitItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Tập thể dục', 'icon' => '💪', 'sort_order' => 1, 'description' => 'Vận động / gym / yoga'],
            ['name' => 'Uống đủ nước', 'icon' => '💧', 'sort_order' => 2, 'description' => 'Hydrate trong ngày'],
            ['name' => 'Đọc sách', 'icon' => '📚', 'sort_order' => 3, 'description' => 'Đọc ít nhất 15 phút'],
            ['name' => 'Ghi trải nghiệm', 'icon' => '📝', 'sort_order' => 4, 'description' => 'Lưu 1 trải nghiệm trên ViVu'],
            ['name' => 'Ăn uống lành mạnh', 'icon' => '🥗', 'sort_order' => 5, 'description' => 'Bữa ăn cân bằng'],
            ['name' => 'Ngủ đủ giấc', 'icon' => '😴', 'sort_order' => 6, 'description' => 'Ngủ trước 23h / đủ 7h'],
            ['name' => 'Thiền / thở', 'icon' => '🧘', 'sort_order' => 7, 'description' => '5–10 phút tĩnh tâm'],
            ['name' => 'Đi bộ', 'icon' => '🚶', 'sort_order' => 8, 'description' => 'Ít nhất 20–30 phút'],
            ['name' => 'Hạn chế MXH', 'icon' => '📵', 'sort_order' => 9, 'description' => 'Giới hạn thời gian scroll'],
            ['name' => 'Học kỹ năng', 'icon' => '🎯', 'sort_order' => 10, 'description' => 'Học / luyện 1 kỹ năng mới'],
        ];

        foreach ($items as $row) {
            HabitItem::query()->updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($row['name'])],
                [
                    'name' => $row['name'],
                    'icon' => $row['icon'],
                    'description' => $row['description'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
