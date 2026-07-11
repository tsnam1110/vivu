<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishCalorieTest extends TestCase
{
    use RefreshDatabase;

    public function test_scales_kcal_by_grams_linearly(): void
    {
        $dish = Dish::factory()->make([
            'calories_kcal' => 450,
            'serving_grams' => 300,
        ]);

        $this->assertTrue($dish->hasCalorieBasis());
        $this->assertSame(450, $dish->caloriesForGrams(300));
        $this->assertSame(150, $dish->caloriesForGrams(100));
        $this->assertSame(900, $dish->caloriesForGrams(600));
        $this->assertSame(150.0, $dish->kcalPer100g());
    }

    public function test_scales_grams_from_kcal(): void
    {
        $dish = Dish::factory()->make([
            'calories_kcal' => 400,
            'serving_grams' => 200,
        ]);

        $this->assertSame(100, $dish->gramsForCalories(200));
        $this->assertSame(200, $dish->gramsForCalories(400));
        $this->assertSame(50, $dish->gramsForCalories(100));
    }

    public function test_clamps_portion_range(): void
    {
        $dish = Dish::factory()->make([
            'calories_kcal' => 100,
            'serving_grams' => 100,
        ]);

        $this->assertSame(Dish::PORTION_GRAMS_MIN, $dish->gramsForCalories(0));
        $this->assertLessThanOrEqual(Dish::PORTION_GRAMS_MAX, $dish->gramsForCalories(999999));
    }
}
