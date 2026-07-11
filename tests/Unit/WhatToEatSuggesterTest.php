<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\Dish;
use App\Services\WhatToEatSuggester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatToEatSuggesterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_by_slot_size_and_mode(): void
    {
        Dish::factory()->create([
            'name' => 'Breakfast Main Cook',
            'meal_slots' => [MealSlot::Breakfast->value],
            'supports_light' => false,
            'supports_main' => true,
            'supports_dine_out' => false,
            'supports_cook_home' => true,
        ]);

        Dish::factory()->create([
            'name' => 'Lunch Light Out',
            'meal_slots' => [MealSlot::Lunch->value],
            'supports_light' => true,
            'supports_main' => false,
            'supports_dine_out' => true,
            'supports_cook_home' => false,
        ]);

        $suggester = app(WhatToEatSuggester::class);

        $result = $suggester->suggest(
            MealSlot::Breakfast,
            MealSize::Main,
            MealMode::CookHome,
            count: 3,
        );

        $this->assertCount(1, $result['dishes']);
        $this->assertSame('Breakfast Main Cook', $result['dishes'][0]['name']);
        $this->assertTrue($result['partial']);
    }

    public function test_respects_count_and_excludes_ids(): void
    {
        $a = Dish::factory()->create([
            'meal_slots' => [MealSlot::Lunch->value],
            'supports_light' => true,
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => true,
        ]);
        $b = Dish::factory()->create([
            'meal_slots' => [MealSlot::Lunch->value],
            'supports_light' => true,
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => true,
        ]);
        Dish::factory()->create([
            'meal_slots' => [MealSlot::Lunch->value],
            'supports_light' => true,
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => true,
        ]);

        $suggester = app(WhatToEatSuggester::class);

        $first = $suggester->suggest(MealSlot::Lunch, MealSize::Main, MealMode::DineOut, 2);
        $this->assertCount(2, $first['dishes']);

        $second = $suggester->suggest(
            MealSlot::Lunch,
            MealSize::Main,
            MealMode::DineOut,
            2,
            excludeIds: [$a->id, $b->id],
        );

        $ids = collect($second['dishes'])->pluck('id')->all();
        $this->assertNotContains($a->id, $ids);
        $this->assertNotContains($b->id, $ids);
    }

    public function test_hidden_dishes_are_not_suggested(): void
    {
        Dish::factory()->hidden()->create([
            'meal_slots' => [MealSlot::Dinner->value],
            'supports_main' => true,
            'supports_dine_out' => true,
        ]);

        $result = app(WhatToEatSuggester::class)->suggest(
            MealSlot::Dinner,
            MealSize::Main,
            MealMode::DineOut,
            3,
        );

        $this->assertSame([], $result['dishes']);
        $this->assertSame(0, $result['total_available']);
    }
}
