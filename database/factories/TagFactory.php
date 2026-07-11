<?php

namespace Database\Factories;

use App\Enums\TagStatus;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'usage_count' => 0,
            'status' => TagStatus::Approved,
            'created_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => TagStatus::Pending,
        ]);
    }
}
