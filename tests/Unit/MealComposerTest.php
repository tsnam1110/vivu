<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\CookingMethod;
use App\Enums\DishRole;
use App\Enums\DishStatus;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\Dish;
use App\Services\MealComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_composes_vn_home_3_with_correct_roles(): void
    {
        $soup = Dish::factory()->create([
            'name' => 'Canh test',
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'calories_kcal' => 80,
            'serving_grams' => 250,
        ]);
        $main = Dish::factory()->create([
            'name' => 'Thịt test',
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'calories_kcal' => 300,
            'serving_grams' => 200,
            'cooking_method' => CookingMethod::Braise,
        ]);
        $veg = Dish::factory()->create([
            'name' => 'Rau test',
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'calories_kcal' => 90,
            'serving_grams' => 150,
        ]);
        // one_bowl should not fill component plate
        Dish::factory()->create([
            'name' => 'Phở test',
            'dish_role' => DishRole::OneBowl,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);

        $pool = Dish::query()->published()->get();
        $result = app(MealComposer::class)->compose(
            pool: $pool,
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
            mealBudget: 700,
        );

        $this->assertTrue($result['ok']);
        $this->assertFalse($result['partial']);
        $this->assertFalse($result['fallback_to_pick']);
        $this->assertSame('vn_home_3', $result['composition']['template_id']);
        $this->assertCount(3, $result['dishes']);
        $roles = collect($result['dishes'])->map(fn (Dish $d) => $d->dish_role)->all();
        $this->assertContains(DishRole::Soup, $roles);
        $this->assertContains(DishRole::MainProtein, $roles);
        $this->assertContains(DishRole::SideVeg, $roles);
        $ids = collect($result['dishes'])->pluck('id');
        $this->assertTrue($ids->contains($soup->id));
        $this->assertTrue($ids->contains($main->id));
        $this->assertTrue($ids->contains($veg->id));
    }

    public function test_auto_breakfast_multi_uses_pick_not_compose(): void
    {
        Dish::factory()->count(3)->create([
            'dish_role' => DishRole::OneBowl,
            'status' => DishStatus::Published,
            'meal_slots' => ['breakfast'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);

        $result = app(\App\Services\WhatToEatSuggester::class)->suggest(
            mealSlot: MealSlot::Breakfast,
            mealSize: MealSize::Main,
            mealMode: MealMode::CookHome,
            count: 3,
            log: false,
            suggestMode: \App\Enums\SuggestMode::Auto,
        );

        $this->assertSame('pick', $result['suggest_mode']);
        $this->assertNull($result['composition']);
        $this->assertCount(3, $result['dishes']);
    }

    public function test_fallback_when_no_roles_in_pool(): void
    {
        Dish::factory()->create([
            'dish_role' => null,
            'status' => DishStatus::Published,
            'meal_slots' => ['lunch'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);

        $result = app(MealComposer::class)->compose(
            pool: Dish::query()->published()->get(),
            slot: MealSlot::Lunch,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
        );

        $this->assertFalse($result['ok']);
        $this->assertTrue($result['fallback_to_pick']);
        $this->assertNull($result['composition']);
    }

    public function test_does_not_put_share_feast_in_home_plate(): void
    {
        Dish::factory()->create([
            'dish_role' => DishRole::ShareFeast,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
        ]);

        $result = app(MealComposer::class)->compose(
            pool: Dish::query()->published()->get(),
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
        );

        $this->assertTrue($result['ok']);
        foreach ($result['dishes'] as $dish) {
            $this->assertNotSame(DishRole::ShareFeast, $dish->dish_role);
        }
    }
}
