<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FiveElement;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\Dish;
use App\Models\MealSuggestionLog;
use App\Models\User;
use App\Models\UserFoodPreference;
use Illuminate\Support\Collection;

class WhatToEatSuggester
{
    public function __construct(
        private readonly WhatToEatPlaceMatcher $placeMatcher,
        private readonly DailyCalorieEstimator $calorieEstimator,
    ) {}

    /**
     * @param  list<int>  $excludeIds
     * @return array{
     *     dishes: list<array<string, mixed>>,
     *     partial: bool,
     *     total_available: int,
     *     count_requested: int,
     *     log_id: int|null,
     *     target_calories: int|null,
     *     meal_budget: int|null
     * }
     */
    public function suggest(
        MealSlot|string $mealSlot,
        MealSize|string $mealSize,
        MealMode|string $mealMode,
        int $count = Dish::COUNT_DEFAULT,
        array $excludeIds = [],
        ?User $user = null,
        ?float $lat = null,
        ?float $lng = null,
        bool $log = true,
        ?int $targetCalories = null,
    ): array {
        $slot = $mealSlot instanceof MealSlot ? $mealSlot : MealSlot::from($mealSlot);
        $size = $mealSize instanceof MealSize ? $mealSize : MealSize::from($mealSize);
        $mode = $mealMode instanceof MealMode ? $mealMode : MealMode::from($mealMode);
        $count = max(Dish::COUNT_MIN, min(Dish::COUNT_MAX, $count));
        $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));

        if ($targetCalories === null && $user) {
            $targetCalories = $this->calorieEstimator->forUser($user)['target_calories'];
        }
        if ($targetCalories !== null) {
            $targetCalories = $this->calorieEstimator->clampDaily($targetCalories);
        }
        $mealBudget = $targetCalories !== null
            ? $this->calorieEstimator->mealBudget($targetCalories, $slot, $size)
            : null;

        $pref = $user ? $this->preferenceFor($user) : null;
        $recentIds = $user ? $this->recentDishIds($user, 7) : [];
        $missingElements = ($user && $pref?->balance_elements)
            ? $this->missingElements($user, 7)
            : [];

        if ($pref?->disliked_dish_ids) {
            $excludeIds = array_values(array_unique(array_merge(
                $excludeIds,
                array_map('intval', $pref->disliked_dish_ids),
            )));
        }

        // Soft-exclude recent (boost later) — hard-exclude only client exclude + disliked
        $candidates = $this->baseQuery($slot, $size, $mode, $pref, $mealBudget)
            ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->get();

        if ($candidates->isEmpty() && $excludeIds !== []) {
            $candidates = $this->baseQuery($slot, $size, $mode, $pref, $mealBudget)->get();
        }

        // Nới filter calo nếu hết pool
        if ($candidates->isEmpty() && $mealBudget !== null) {
            $candidates = $this->baseQuery($slot, $size, $mode, $pref, null)
                ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->get();
        }

        if ($candidates->isEmpty()) {
            return [
                'dishes' => [],
                'partial' => true,
                'total_available' => 0,
                'count_requested' => $count,
                'log_id' => null,
                'target_calories' => $targetCalories,
                'meal_budget' => $mealBudget,
            ];
        }

        $scored = $candidates
            ->map(fn (Dish $dish) => [
                'dish' => $dish,
                'score' => $this->score($dish, $mode, $pref, $recentIds, $missingElements, $mealBudget),
            ])
            ->sortByDesc('score')
            ->values();

        $picked = $this->pickTopWithJitter($scored, $count);
        $reason = $this->buildReason($slot, $size, $mode, $mealBudget);

        $cards = $picked->map(function (array $row) use ($reason, $mode, $lat, $lng) {
            /** @var Dish $dish */
            $dish = $row['dish'];
            $dish->increment('suggest_count');

            $card = $dish->toSuggestionCard($reason);

            if ($mode === MealMode::DineOut) {
                $places = $this->placeMatcher->findPlaces($dish, $lat, $lng, 3);
                $card['places_count'] = count($places);
                $card['places_preview'] = array_slice($places, 0, 1);
            } else {
                $card['places_count'] = 0;
                $card['places_preview'] = [];
            }

            return $card;
        })->all();

        $logId = null;
        if ($log && $user) {
            $logId = MealSuggestionLog::query()->create([
                'user_id' => $user->id,
                'meal_slot' => $slot->value,
                'meal_size' => $size->value,
                'meal_mode' => $mode->value,
                'filters_json' => [
                    'count' => $count,
                    'exclude_ids' => $excludeIds,
                    'lat' => $lat,
                    'lng' => $lng,
                    'target_calories' => $targetCalories,
                    'meal_budget' => $mealBudget,
                ],
                'suggested_dish_ids' => array_column($cards, 'id'),
                'chosen_dish_id' => null,
                'outcome' => 'suggested',
                'created_at' => now(),
            ])->id;
        }

        return [
            'dishes' => $cards,
            'partial' => count($cards) < $count,
            'total_available' => $candidates->count(),
            'count_requested' => $count,
            'log_id' => $logId,
            'target_calories' => $targetCalories,
            'meal_budget' => $mealBudget,
        ];
    }

    public function choose(User $user, int $logId, Dish $dish): MealSuggestionLog
    {
        $log = MealSuggestionLog::query()
            ->where('user_id', $user->id)
            ->whereKey($logId)
            ->firstOrFail();

        $log->update([
            'chosen_dish_id' => $dish->id,
            'outcome' => 'chosen',
        ]);

        return $log->fresh(['chosenDish']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function history(User $user, int $limit = 30): array
    {
        return MealSuggestionLog::query()
            ->where('user_id', $user->id)
            ->with('chosenDish')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (MealSuggestionLog $log) {
                $ids = $log->suggested_dish_ids ?? [];
                $dishes = Dish::query()->whereIn('id', $ids)->get()->keyBy('id');

                return [
                    'id' => $log->id,
                    'meal_slot' => $log->meal_slot,
                    'meal_size' => $log->meal_size,
                    'meal_mode' => $log->meal_mode,
                    'outcome' => $log->outcome,
                    'created_at' => $log->created_at?->timezone(config('app.timezone'))->toIso8601String(),
                    'suggested' => collect($ids)->map(function ($id) use ($dishes) {
                        $d = $dishes->get($id);

                        return $d ? [
                            'id' => $d->id,
                            'name' => $d->name,
                            'slug' => $d->slug,
                            'emoji' => $d->emoji ?: '🍽️',
                        ] : null;
                    })->filter()->values()->all(),
                    'chosen' => $log->chosenDish ? [
                        'id' => $log->chosenDish->id,
                        'name' => $log->chosenDish->name,
                        'slug' => $log->chosenDish->slug,
                        'emoji' => $log->chosenDish->emoji ?: '🍽️',
                    ] : null,
                ];
            })
            ->all();
    }

    public function preferenceFor(User $user): ?UserFoodPreference
    {
        return UserFoodPreference::query()->where('user_id', $user->id)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsertPreference(User $user, array $data): UserFoodPreference
    {
        return UserFoodPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data,
        );
    }

    private function baseQuery(
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        ?UserFoodPreference $pref,
        ?int $mealBudget = null,
    ) {
        $q = Dish::query()
            ->published()
            ->forMealSlot($slot)
            ->forMealSize($size)
            ->forMealMode($mode);

        // Ngân sách bữa (từ target ngày) ưu tiên hơn max_calories_default của pref
        $max = $mealBudget;
        if ($max === null && $pref?->max_calories_default) {
            $max = (int) $pref->max_calories_default;
        }
        if ($max !== null) {
            // Cho phép vượt ~25% để còn lựa chọn
            $softMax = (int) round($max * 1.25);
            $q->where(function ($builder) use ($softMax) {
                $builder->whereNull('calories_kcal')
                    ->orWhere('calories_kcal', '<=', $softMax);
            });
        }

        // Lightweight diet: vegetarian prefers dishes without meat keywords in search
        $flags = $pref?->diet_flags ?? [];
        if (in_array('vegetarian', $flags, true)) {
            $q->where(function ($builder) {
                $builder->where('search_keywords', 'like', '%chay%')
                    ->orWhere('search_keywords', 'like', '%vegetarian%')
                    ->orWhere('name', 'like', '%chay%')
                    ->orWhere('slug', 'like', '%chay%')
                    ->orWhere('slug', 'like', '%salad%')
                    ->orWhere('slug', 'like', '%trai-cay%')
                    ->orWhere('slug', 'like', '%sua-chua%')
                    ->orWhere('slug', 'like', '%yen-mach%')
                    ->orWhere('slug', 'like', '%rau%');
            });
        }

        return $q;
    }

    /**
     * @param  list<int>  $recentIds
     * @param  list<string>  $missingElements
     */
    private function score(
        Dish $dish,
        MealMode $mode,
        ?UserFoodPreference $pref,
        array $recentIds,
        array $missingElements,
        ?int $mealBudget = null,
    ): float {
        $score = 40.0;

        if ($mode === MealMode::CookHome && $dish->hasRecipe()) {
            $score += 20;
        }

        if ($dish->calories_kcal !== null) {
            $score += 5;
            if ($mealBudget !== null && $mealBudget > 0) {
                // Càng gần ngân sách bữa càng cao điểm (tối đa +20)
                $ratio = abs($dish->calories_kcal - $mealBudget) / $mealBudget;
                $score += max(0, 20 - ($ratio * 30));
            }
        }

        if ($dish->five_element !== null) {
            $score += 3;
            if ($pref?->preferred_elements && in_array($dish->five_element->value, $pref->preferred_elements, true)) {
                $score += 12;
            }
            if ($missingElements !== [] && in_array($dish->five_element->value, $missingElements, true)) {
                $score += 10;
            }
        }

        if (in_array($dish->id, $recentIds, true)) {
            $score -= 15;
        }

        $score += min(10.0, log(1 + $dish->suggest_count) * 2);
        $score += random_int(0, 50) / 10;

        return $score;
    }

    /**
     * @param  Collection<int, array{dish: Dish, score: float}>  $scored
     * @return Collection<int, array{dish: Dish, score: float}>
     */
    private function pickTopWithJitter(Collection $scored, int $count): Collection
    {
        if ($scored->count() <= $count) {
            return $scored;
        }

        $bandSize = min($scored->count(), max($count * 3, $count + 4));

        return $scored->take($bandSize)->shuffle()->take($count)->values();
    }

    private function buildReason(MealSlot $slot, MealSize $size, MealMode $mode, ?int $mealBudget = null): string
    {
        $base = __('what_to_eat.reason_template', [
            'slot' => $slot->label(),
            'size' => $size->label(),
            'mode' => $mode->label(),
        ]);

        if ($mealBudget !== null) {
            $base .= ' · '.__('what_to_eat.reason_budget', ['kcal' => $mealBudget]);
        }

        return $base;
    }

    /**
     * @return list<int>
     */
    private function recentDishIds(User $user, int $days): array
    {
        $since = now()->subDays($days);

        $rows = MealSuggestionLog::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['suggested_dish_ids', 'chosen_dish_id']);

        $ids = [];
        foreach ($rows as $row) {
            if ($row->chosen_dish_id) {
                $ids[] = (int) $row->chosen_dish_id;
            }
            foreach ($row->suggested_dish_ids ?? [] as $id) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<string>
     */
    private function missingElements(User $user, int $days): array
    {
        $all = array_map(fn (FiveElement $e) => $e->value, FiveElement::cases());
        $since = now()->subDays($days);

        $chosenIds = MealSuggestionLog::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->whereNotNull('chosen_dish_id')
            ->pluck('chosen_dish_id')
            ->all();

        if ($chosenIds === []) {
            return $all;
        }

        $present = Dish::query()
            ->whereIn('id', $chosenIds)
            ->whereNotNull('five_element')
            ->pluck('five_element')
            ->map(fn ($e) => $e instanceof FiveElement ? $e->value : (string) $e)
            ->unique()
            ->all();

        return array_values(array_diff($all, $present));
    }
}
