<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\User;
use App\Models\UserProfile;

/**
 * Ước lượng calo ngày / bữa từ hồ sơ cá nhân (tham khảo, không y khoa).
 */
class DailyCalorieEstimator
{
    public const PRESETS = [1500, 2000, 2500];

    public const DEFAULT_FALLBACK = 2000;

    public const MIN_DAILY = 1000;

    public const MAX_DAILY = 5000;

    /**
     * @return array{
     *     target_calories: int,
     *     source: string,
     *     weight_kg: float|null,
     *     has_body_metrics: bool,
     *     meal_budget_hint: int|null,
     *     presets: list<int>
     * }
     */
    public function forUser(?User $user, ?MealSlot $slot = null, ?MealSize $size = null): array
    {
        $profile = $user?->profile;
        $daily = $this->estimateDaily($profile);
        $source = $daily['source'];
        $target = $daily['kcal'];

        $mealBudget = null;
        if ($slot !== null && $size !== null) {
            $mealBudget = $this->mealBudget($target, $slot, $size);
        }

        return [
            'target_calories' => $target,
            'source' => $source,
            'weight_kg' => $profile?->weight_kg !== null ? (float) $profile->weight_kg : null,
            'has_body_metrics' => $profile !== null && $profile->weight_kg !== null,
            'meal_budget_hint' => $mealBudget,
            'presets' => self::PRESETS,
        ];
    }

    /**
     * @return array{kcal: int, source: string}
     */
    public function estimateDaily(?UserProfile $profile): array
    {
        if ($profile === null || $profile->weight_kg === null || (float) $profile->weight_kg <= 0) {
            return ['kcal' => self::DEFAULT_FALLBACK, 'source' => 'fallback'];
        }

        $weight = (float) $profile->weight_kg;
        $height = $profile->height_cm ? (int) $profile->height_cm : null;
        $age = $this->ageFromBirthYear($profile->birth_year);
        $gender = $profile->gender;
        $activity = $profile->activity_level ?? ActivityLevel::Sedentary;

        if ($height !== null && $height >= 100 && $age !== null) {
            $bmr = $this->mifflinBmr($weight, $height, $age, $gender);
            $kcal = (int) round($bmr * $activity->multiplier());
            $source = 'mifflin';
        } else {
            // Rule of thumb: ~30 kcal / kg cân nặng (duy trì, mức trung bình)
            $kcal = (int) round($weight * 30);
            $source = 'weight';
        }

        return [
            'kcal' => $this->clampDaily($kcal),
            'source' => $source,
        ];
    }

    public function mealBudget(int $dailyKcal, MealSlot $slot, MealSize $size): int
    {
        $dailyKcal = $this->clampDaily($dailyKcal);

        $pct = match ($slot) {
            MealSlot::Breakfast => 0.25,
            MealSlot::Lunch => 0.35,
            MealSlot::Dinner => 0.40,
        };

        if ($size === MealSize::Light) {
            $pct *= 0.55;
        }

        return max(80, (int) round($dailyKcal * $pct));
    }

    public function clampDaily(int $kcal): int
    {
        return max(self::MIN_DAILY, min(self::MAX_DAILY, $kcal));
    }

    private function ageFromBirthYear(?int $birthYear): ?int
    {
        if ($birthYear === null || $birthYear < 1920) {
            return null;
        }

        $age = (int) now(config('app.timezone'))->year - $birthYear;

        return ($age >= 10 && $age <= 100) ? $age : null;
    }

    private function mifflinBmr(float $weightKg, int $heightCm, int $age, ?Gender $gender): float
    {
        // Mifflin–St Jeor
        $base = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);

        return match ($gender) {
            Gender::Male => $base + 5,
            Gender::Female => $base - 161,
            default => $base - 78, // trung bình male/female
        };
    }
}
