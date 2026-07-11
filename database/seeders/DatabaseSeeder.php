<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            TasteTraitSeeder::class,
            SampleAvatarSeeder::class,
            AvatarFrameSeeder::class,
            HabitItemSeeder::class,
            DishSeeder::class,
        ]);
    }
}
