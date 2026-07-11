<?php

namespace Database\Factories;

use App\Enums\TraitType;
use App\Models\TasteTrait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TasteTrait>
 */
class TasteTraitFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'type' => fake()->randomElement([TraitType::Personality, TraitType::Interest]),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'is_active' => true,
        ];
    }
}
