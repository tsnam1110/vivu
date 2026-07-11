<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DishStatus;
use App\Enums\MealSlot;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatToEatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_suggest(): void
    {
        $this->postJson(route('what-to-eat.suggest'), [
            'meal_slot' => 'lunch',
            'meal_size' => 'main',
            'meal_mode' => 'dine_out',
            'count' => 3,
        ])->assertUnauthorized();
    }

    public function test_user_can_suggest_dishes(): void
    {
        $user = User::factory()->create();
        Dish::factory()->count(5)->create([
            'meal_slots' => [MealSlot::Lunch->value],
            'supports_main' => true,
            'supports_dine_out' => true,
            'status' => DishStatus::Published,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'dine_out',
                'count' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('meta.count_requested', 3)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'slug', 'emoji', 'reason'],
                ],
                'meta' => ['partial', 'total_available', 'count_requested'],
            ]);
    }

    public function test_count_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'cook_home',
                'count' => 99,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function test_user_can_view_dish_detail(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create([
            'name' => 'Phở test',
            'slug' => 'pho-test',
            'benefits' => 'Ấm bụng',
            'status' => DishStatus::Published,
        ]);

        $this->actingAs($user, 'web')
            ->getJson(route('what-to-eat.dishes.show', $dish))
            ->assertOk()
            ->assertJsonPath('data.slug', 'pho-test')
            ->assertJsonPath('data.benefits', 'Ấm bụng')
            ->assertJsonStructure(['data', 'meta' => ['disclaimer']]);
    }

    public function test_hidden_dish_detail_is_not_found(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->hidden()->create(['slug' => 'hidden-dish']);

        $this->actingAs($user, 'web')
            ->getJson(route('what-to-eat.dishes.show', $dish))
            ->assertNotFound();
    }

    public function test_home_vault_contains_what_to_eat_trigger(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('home'))
            ->assertOk()
            ->assertSee(__('what_to_eat.trigger_label'), false);
    }
}
