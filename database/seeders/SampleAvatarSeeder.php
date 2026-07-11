<?php

namespace Database\Seeders;

use App\Models\SampleAvatar;
use Illuminate\Database\Seeder;

class SampleAvatarSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['slug' => 'explorer', 'name' => 'Nhà thám hiểm', 'path' => 'images/sample-avatars/explorer.svg', 'sort_order' => 10],
            ['slug' => 'foodie', 'name' => 'Foodie', 'path' => 'images/sample-avatars/foodie.svg', 'sort_order' => 20],
            ['slug' => 'nomad', 'name' => 'Digital nomad', 'path' => 'images/sample-avatars/nomad.svg', 'sort_order' => 30],
            ['slug' => 'beach', 'name' => 'Biển xanh', 'path' => 'images/sample-avatars/beach.svg', 'sort_order' => 40],
            ['slug' => 'mountain', 'name' => 'Núi rừng', 'path' => 'images/sample-avatars/mountain.svg', 'sort_order' => 50],
            ['slug' => 'cafe', 'name' => 'Cà phê', 'path' => 'images/sample-avatars/cafe.svg', 'sort_order' => 60],
        ];

        foreach ($samples as $sample) {
            SampleAvatar::query()->updateOrCreate(
                ['slug' => $sample['slug']],
                $sample + ['is_active' => true],
            );
        }

        SampleAvatar::flushCache();
    }
}
