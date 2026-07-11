<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DishStatus;
use App\Enums\FiveElement;
use App\Enums\MealSlot;
use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    protected $model = Dish::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'emoji' => '🍜',
            'summary' => fake()->sentence(),
            'meal_slots' => [MealSlot::Lunch->value, MealSlot::Dinner->value],
            'supports_light' => true,
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => true,
            'five_element' => fake()->randomElement(FiveElement::cases()),
            'calories_kcal' => fake()->numberBetween(200, 700),
            'serving_grams' => fake()->numberBetween(150, 450),
            'cook_minutes' => fake()->numberBetween(10, 45),
            'ingredients' => [
                ['name' => 'Gạo', 'amount' => '1 chén'],
                ['name' => 'Muối', 'amount' => '1 nhúm'],
            ],
            'steps' => ['Chuẩn bị nguyên liệu', 'Chế biến', 'Thưởng thức'],
            'benefits' => 'Cung cấp năng lượng.',
            'harms' => 'Ăn quá nhiều có thể no lâu.',
            'advice' => 'Dùng khi còn nóng.',
            'notes' => null,
            'search_keywords' => 'test',
            'status' => DishStatus::Published,
            'source' => 'system',
            'suggest_count' => 0,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['status' => DishStatus::Hidden]);
    }

    public function breakfastMain(): static
    {
        return $this->state(fn () => [
            'meal_slots' => [MealSlot::Breakfast->value],
            'supports_light' => false,
            'supports_main' => true,
        ]);
    }
}
