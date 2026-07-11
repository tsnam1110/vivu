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
                'suggest_mode' => 'pick',
            ])
            ->assertOk()
            ->assertJsonPath('meta.count_requested', 3)
            ->assertJsonPath('meta.suggest_mode', 'pick')
            ->assertJsonPath('meta.ruleset_version', config('what_to_eat.ruleset_version'))
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'slug', 'emoji', 'reason'],
                ],
                'meta' => ['partial', 'total_available', 'count_requested', 'suggest_mode', 'ruleset_version'],
            ]);
    }

    public function test_ruleset_version_is_semver_synced(): void
    {
        $this->assertSame('0.3.0', config('what_to_eat.ruleset_version'));
    }

    public function test_compose_returns_plate_composition(): void
    {
        $user = User::factory()->create();
        Dish::factory()->create([
            'meal_slots' => ['dinner'],
            'supports_light' => true,
            'supports_main' => false,
            'supports_cook_home' => true,
            'dish_role' => \App\Enums\DishRole::Soup,
            'status' => DishStatus::Published,
        ]);
        Dish::factory()->create([
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'dish_role' => \App\Enums\DishRole::MainProtein,
            'status' => DishStatus::Published,
        ]);
        Dish::factory()->create([
            'meal_slots' => ['dinner'],
            'supports_light' => true,
            'supports_main' => false,
            'supports_cook_home' => true,
            'dish_role' => \App\Enums\DishRole::SideVeg,
            'status' => DishStatus::Published,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'dinner',
                'meal_size' => 'main',
                'meal_mode' => 'cook_home',
                'count' => 3,
                'suggest_mode' => 'compose',
            ])
            ->assertOk()
            ->assertJsonPath('meta.suggest_mode', 'compose')
            ->assertJsonPath('meta.composition.template_id', 'vn_home_3')
            ->assertJsonCount(3, 'data');
    }

    public function test_suggest_filters_by_culinary_region(): void
    {
        $user = User::factory()->create();
        Dish::factory()->create([
            'name' => 'Bắc only',
            'meal_slots' => ['lunch'],
            'supports_main' => true,
            'supports_dine_out' => true,
            'culinary_regions' => ['bac'],
            'status' => DishStatus::Published,
        ]);
        Dish::factory()->create([
            'name' => 'Nam only',
            'meal_slots' => ['lunch'],
            'supports_main' => true,
            'supports_dine_out' => true,
            'culinary_regions' => ['nam'],
            'status' => DishStatus::Published,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'dine_out',
                'count' => 5,
                'suggest_mode' => 'pick',
                'culinary_region' => 'bac',
            ])
            ->assertOk()
            ->assertJsonPath('meta.relaxations', []);

        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Bắc only'));
        $this->assertFalse($names->contains('Nam only'));
    }

    public function test_suggest_exposes_region_relaxation_in_meta(): void
    {
        $user = User::factory()->create();
        Dish::factory()->create([
            'name' => 'Only Nam',
            'meal_slots' => ['lunch'],
            'supports_main' => true,
            'supports_dine_out' => true,
            'culinary_regions' => ['nam'],
            'status' => DishStatus::Published,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'dine_out',
                'count' => 2,
                'suggest_mode' => 'pick',
                'culinary_region' => 'bac',
            ])
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Only Nam')
            ->assertJsonPath('meta.relaxations.0', 'culinary_region');

        $message = $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'dine_out',
                'count' => 1,
                'suggest_mode' => 'pick',
                'culinary_region' => 'bac',
            ])
            ->assertOk()
            ->json('meta.message');

        $this->assertIsString($message);
        $this->assertNotSame('', $message);
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
