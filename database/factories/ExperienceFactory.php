<?php

namespace Database\Factories;

use App\Enums\ExperienceStatus;
use App\Models\Category;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'content' => fake()->paragraphs(3, true),
            'place_name' => fake()->company(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(8, 23),
            'longitude' => fake()->longitude(102, 110),
            'google_place_id' => null,
            'status' => ExperienceStatus::Published,
            'rating_avg' => 0,
            'rating_count' => 0,
            'reaction_count' => 0,
            'view_count' => 0,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ExperienceStatus::Draft,
            'published_at' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => [
            'status' => ExperienceStatus::Hidden,
        ]);
    }
}
