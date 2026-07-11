<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\UserProfile;
use App\Services\DailyCalorieEstimator;
use Tests\TestCase;

class DailyCalorieEstimatorTest extends TestCase
{
    public function test_fallback_without_weight(): void
    {
        $est = app(DailyCalorieEstimator::class);
        $r = $est->estimateDaily(null);

        $this->assertSame(2000, $r['kcal']);
        $this->assertSame('fallback', $r['source']);
    }

    public function test_weight_rule_of_thumb(): void
    {
        $profile = new UserProfile([
            'weight_kg' => 60,
        ]);

        $r = app(DailyCalorieEstimator::class)->estimateDaily($profile);

        $this->assertSame(1800, $r['kcal']); // 60 * 30
        $this->assertSame('weight', $r['source']);
    }

    public function test_mifflin_when_full_metrics(): void
    {
        $profile = new UserProfile([
            'weight_kg' => 70,
            'height_cm' => 175,
            'birth_year' => now()->year - 30,
            'gender' => Gender::Male,
            'activity_level' => ActivityLevel::Sedentary,
        ]);

        $r = app(DailyCalorieEstimator::class)->estimateDaily($profile);

        $this->assertSame('mifflin', $r['source']);
        $this->assertGreaterThan(1500, $r['kcal']);
        $this->assertLessThan(3000, $r['kcal']);
    }

    public function test_meal_budget_splits_daily(): void
    {
        $est = app(DailyCalorieEstimator::class);

        $lunch = $est->mealBudget(2000, MealSlot::Lunch, MealSize::Main);
        $light = $est->mealBudget(2000, MealSlot::Lunch, MealSize::Light);

        $this->assertSame(700, $lunch); // 35%
        $this->assertLessThan($lunch, $light);
    }
}
