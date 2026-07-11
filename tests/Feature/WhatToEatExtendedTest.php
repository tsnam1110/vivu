<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Enums\DishStatus;
use App\Enums\ExperienceStatus;
use App\Enums\MealSlot;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Dish;
use App\Models\DishContribution;
use App\Models\Experience;
use App\Models\MealSuggestionLog;
use App\Models\User;
use App\Services\DishContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WhatToEatExtendedTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_logs_for_user(): void
    {
        $user = User::factory()->create();
        Dish::factory()->count(3)->create([
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
                'count' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('meta.count_requested', 2);

        $this->assertDatabaseCount('meal_suggestion_logs', 1);
        $this->assertSame($user->id, MealSuggestionLog::query()->first()->user_id);
    }

    public function test_user_can_choose_from_log(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['status' => DishStatus::Published]);
        $log = MealSuggestionLog::query()->create([
            'user_id' => $user->id,
            'meal_slot' => 'lunch',
            'meal_size' => 'main',
            'meal_mode' => 'dine_out',
            'filters_json' => ['count' => 1],
            'suggested_dish_ids' => [$dish->id],
            'outcome' => 'suggested',
            'created_at' => now(),
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.choose'), [
                'log_id' => $log->id,
                'dish_id' => $dish->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.outcome', 'chosen');

        $this->assertSame($dish->id, $log->fresh()->chosen_dish_id);
    }

    public function test_user_can_contribute_pending(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create(['status' => DishStatus::Published, 'slug' => 'pho-contrib']);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.dishes.contribute', $dish), [
                'type' => 'benefit',
                'payload' => ['body' => 'Giàu protein, dễ tiêu.'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('dish_contributions', [
            'dish_id' => $dish->id,
            'user_id' => $user->id,
            'status' => ContributionStatus::Pending->value,
        ]);
    }

    public function test_admin_approve_syncs_canonical_to_dish(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $dish = Dish::factory()->create([
            'status' => DishStatus::Published,
            'calories_kcal' => 100,
            'serving_grams' => 100,
        ]);

        $contribution = DishContribution::query()->create([
            'dish_id' => $dish->id,
            'user_id' => $user->id,
            'type' => ContributionType::Calories,
            'payload' => ['kcal_per_serving' => 420, 'serving_grams' => 400],
            'status' => ContributionStatus::Pending,
        ]);

        app(DishContributionService::class)->approve($contribution, $admin, true);

        $this->assertSame(420, $dish->fresh()->calories_kcal);
        $this->assertSame(400, $dish->fresh()->serving_grams);
        $this->assertTrue($contribution->fresh()->is_canonical);
        $this->assertSame(ContributionStatus::Approved, $contribution->fresh()->status);
    }

    public function test_dish_detail_exposes_calorie_basis_and_scale(): void
    {
        $user = User::factory()->create();
        $dish = Dish::factory()->create([
            'status' => DishStatus::Published,
            'slug' => 'com-test-kcal',
            'calories_kcal' => 500,
            'serving_grams' => 400,
        ]);

        $this->assertSame(250, $dish->caloriesForGrams(200));
        $this->assertSame(200, $dish->gramsForCalories(250));
        $this->assertSame(125.0, $dish->kcalPer100g());

        $this->actingAs($user, 'web')
            ->getJson(route('what-to-eat.dishes.show', $dish))
            ->assertOk()
            ->assertJsonPath('data.calories_kcal', 500)
            ->assertJsonPath('data.serving_grams', 400)
            ->assertJsonPath('data.has_calorie_basis', true)
            ->assertJsonPath('data.kcal_per_100g', 125);
    }

    public function test_admin_can_list_and_approve_via_api(): void
    {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin, ['*'], 'admin');

        $user = User::factory()->create();
        $dish = Dish::factory()->create(['status' => DishStatus::Published]);
        $contribution = DishContribution::query()->create([
            'dish_id' => $dish->id,
            'user_id' => $user->id,
            'type' => ContributionType::Advice,
            'payload' => ['body' => 'Ăn nóng sẽ ngon hơn.'],
            'status' => ContributionStatus::Pending,
        ]);

        $this->getJson('/api/admin/dish-contributions?status=pending')
            ->assertOk()
            ->assertJsonFragment(['id' => $contribution->id]);

        $this->patchJson("/api/admin/dish-contributions/{$contribution->id}/status", [
            'status' => 'approved',
            'set_canonical' => true,
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertSame('Ăn nóng sẽ ngon hơn.', $dish->fresh()->advice);
    }

    public function test_admin_can_crud_dishes(): void
    {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin, ['*'], 'admin');

        $this->postJson('/api/admin/dishes', [
            'name' => 'Bún test admin',
            'meal_slots' => ['lunch', 'dinner'],
            'supports_light' => false,
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => false,
            'status' => 'published',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Bún test admin');

        $this->getJson('/api/admin/dishes')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Bún test admin']);
    }

    public function test_detail_includes_matching_places(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $dish = Dish::factory()->create([
            'name' => 'Phở bò',
            'slug' => 'pho-bo-place',
            'search_keywords' => 'pho bo noodle',
            'status' => DishStatus::Published,
        ]);

        Experience::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Quán phở ngon',
            'place_name' => 'Phở Hà Nội',
            'status' => ExperienceStatus::Published,
            'latitude' => 21.0,
            'longitude' => 105.8,
            'published_at' => now(),
        ]);

        $this->actingAs($user, 'web')
            ->getJson(route('what-to-eat.dishes.show', $dish))
            ->assertOk()
            ->assertJsonPath('data.slug', 'pho-bo-place');
    }

    public function test_history_page_and_preferences(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('what-to-eat.history'))
            ->assertOk()
            ->assertSee(__('what_to_eat.history'), false);

        $this->actingAs($user, 'web')
            ->putJson(route('what-to-eat.preferences.update'), [
                'diet_flags' => ['vegetarian'],
                'balance_elements' => true,
                'max_calories_default' => 500,
            ])
            ->assertOk()
            ->assertJsonPath('data.balance_elements', true);

        $this->assertDatabaseHas('user_food_preferences', [
            'user_id' => $user->id,
            'balance_elements' => 1,
        ]);
    }
}
