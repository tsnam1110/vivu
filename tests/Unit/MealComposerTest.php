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

    public function test_soft_diversity_prefers_non_duplicate_fry_and_protein(): void
    {
        Dish::factory()->create([
            'name' => 'Canh A',
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'cooking_method' => CookingMethod::Boil,
            'protein_source' => \App\Enums\ProteinSource::None,
            'calories_kcal' => 60,
            'serving_grams' => 250,
        ]);
        $fryMeat = Dish::factory()->create([
            'name' => 'Thịt chiên',
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'cooking_method' => CookingMethod::Fry,
            'protein_source' => \App\Enums\ProteinSource::Meat,
            'calories_kcal' => 320,
            'serving_grams' => 180,
        ]);
        // Second main option: not fry, different protein — should win soft score when band allows
        Dish::factory()->create([
            'name' => 'Cá kho',
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'cooking_method' => CookingMethod::Braise,
            'protein_source' => \App\Enums\ProteinSource::Seafood,
            'calories_kcal' => 280,
            'serving_grams' => 180,
        ]);
        Dish::factory()->create([
            'name' => 'Rau luộc',
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'cooking_method' => CookingMethod::Boil,
            'protein_source' => \App\Enums\ProteinSource::Plant,
            'calories_kcal' => 40,
            'serving_grams' => 150,
        ]);
        // Extra fry veg would double-fry if chosen after fry main
        Dish::factory()->create([
            'name' => 'Rau chiên',
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'cooking_method' => CookingMethod::Fry,
            'protein_source' => \App\Enums\ProteinSource::Plant,
            'calories_kcal' => 120,
            'serving_grams' => 120,
        ]);

        $hitsDoubleFry = 0;
        for ($i = 0; $i < 12; $i++) {
            $result = app(MealComposer::class)->compose(
                pool: Dish::query()->published()->get(),
                slot: MealSlot::Dinner,
                size: MealSize::Main,
                mode: MealMode::CookHome,
                count: 3,
                mealBudget: 700,
            );
            $this->assertTrue($result['ok']);
            $fry = collect($result['dishes'])->filter(fn (Dish $d) => $d->cooking_method === CookingMethod::Fry)->count();
            if ($fry >= 2) {
                $hitsDoubleFry++;
            }
            $ruleIds = collect($result['composition']['explanations'] ?? [])->pluck('rule_id')->all();
            $this->assertContains('E01_protein_diversity', $ruleIds);
        }
        // Soft only: allow rare double fry but should be uncommon
        $this->assertLessThanOrEqual(4, $hitsDoubleFry);
        unset($fryMeat);
    }

    public function test_dine_out_feast_template_when_count_ge_2(): void
    {
        Dish::factory()->create([
            'name' => 'Lẩu test',
            'dish_role' => DishRole::ShareFeast,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_dine_out' => true,
            'supports_cook_home' => false,
        ]);
        Dish::factory()->create([
            'name' => 'Phở test dine',
            'dish_role' => DishRole::OneBowl,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_dine_out' => true,
        ]);

        $result = app(MealComposer::class)->compose(
            pool: Dish::query()->published()->get(),
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::DineOut,
            count: 3,
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('dine_out_feast_1', $result['composition']['template_id']);
        $this->assertSame(DishRole::ShareFeast, $result['dishes'][0]->dish_role);
    }

    public function test_exclude_plate_signature_rerolls_when_pool_allows(): void
    {
        Dish::factory()->create([
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Canh 1',
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Canh 2',
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Mặn 1',
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Mặn 2',
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Rau 1',
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'name' => 'Rau 2',
        ]);

        $composer = app(MealComposer::class);
        $pool = Dish::query()->published()->get();
        $first = $composer->compose(
            pool: $pool,
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
        );
        $this->assertTrue($first['ok']);
        $sig = $first['composition']['signature'];

        $different = 0;
        for ($i = 0; $i < 10; $i++) {
            $next = $composer->compose(
                pool: $pool,
                slot: MealSlot::Dinner,
                size: MealSize::Main,
                mode: MealMode::CookHome,
                count: 3,
                excludePlateSignatures: [$sig],
            );
            $this->assertTrue($next['ok']);
            if (($next['composition']['signature'] ?? '') !== $sig) {
                $different++;
            }
        }
        $this->assertGreaterThanOrEqual(1, $different);
    }

    public function test_thermal_explanations_only_when_soft_yhct_on(): void
    {
        Dish::factory()->create([
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'thermal_nature' => \App\Enums\ThermalNature::Hot,
            'calories_kcal' => 80,
            'serving_grams' => 250,
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::MainProtein,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'thermal_nature' => \App\Enums\ThermalNature::Warm,
            'calories_kcal' => 300,
            'serving_grams' => 200,
        ]);
        Dish::factory()->create([
            'dish_role' => DishRole::SideVeg,
            'status' => DishStatus::Published,
            'meal_slots' => ['dinner'],
            'supports_main' => true,
            'supports_cook_home' => true,
            'thermal_nature' => \App\Enums\ThermalNature::Hot,
            'calories_kcal' => 50,
            'serving_grams' => 150,
        ]);

        $off = app(MealComposer::class)->compose(
            pool: Dish::query()->published()->get(),
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
            softYhct: false,
        );
        $on = app(MealComposer::class)->compose(
            pool: Dish::query()->published()->get(),
            slot: MealSlot::Dinner,
            size: MealSize::Main,
            mode: MealMode::CookHome,
            count: 3,
            softYhct: true,
        );

        $idsOff = collect($off['composition']['explanations'] ?? [])->pluck('rule_id')->all();
        $idsOn = collect($on['composition']['explanations'] ?? [])->pluck('rule_id')->all();
        $this->assertNotContains('C01_no_all_hot', $idsOff);
        $this->assertContains('C01_no_all_hot', $idsOn);
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
