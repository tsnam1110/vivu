<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CulinaryRegion;
use App\Enums\FiveElement;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Enums\SuggestMode;
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
        private readonly MealComposer $composer,
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
     *     meal_budget: int|null,
     *     suggest_mode: string,
     *     composition: array<string, mixed>|null,
     *     message: string|null,
     *     relaxations: list<string>
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
        SuggestMode|string $suggestMode = SuggestMode::Auto,
        ?string $culinaryRegion = null,
        array $excludePlateSignatures = [],
    ): array {
        $slot = $mealSlot instanceof MealSlot ? $mealSlot : MealSlot::from($mealSlot);
        $size = $mealSize instanceof MealSize ? $mealSize : MealSize::from($mealSize);
        $mode = $mealMode instanceof MealMode ? $mealMode : MealMode::from($mealMode);
        $modeReq = $suggestMode instanceof SuggestMode ? $suggestMode : SuggestMode::from($suggestMode);
        $count = max(Dish::COUNT_MIN, min(Dish::COUNT_MAX, $count));
        $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
        $excludePlateSignatures = array_values(array_unique(array_filter(array_map('strval', $excludePlateSignatures))));
        $culinaryRegion = $culinaryRegion !== null && $culinaryRegion !== '' ? $culinaryRegion : null;

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
        $softYhct = (bool) ($pref?->balance_elements);
        $recentIds = $user ? $this->recentDishIds($user, 7) : [];
        $missingElements = ($user && $softYhct)
            ? $this->missingElements($user, 7)
            : [];

        if ($pref?->disliked_dish_ids) {
            $excludeIds = array_values(array_unique(array_merge(
                $excludeIds,
                array_map('intval', $pref->disliked_dish_ids),
            )));
        }

        $resolvedMode = $this->resolveSuggestMode($modeReq, $slot, $size, $mode, $count);

        // Compose: không lọc meal_size (canh/rau có thể supports_light only nhưng thuộc mâm chính).
        $loaded = $this->loadCandidates(
            $slot,
            $size,
            $mode,
            $pref,
            $mealBudget,
            $excludeIds,
            $culinaryRegion,
            skipSizeFilter: $resolvedMode === SuggestMode::Compose,
        );
        $candidates = $loaded['candidates'];
        $relaxations = $loaded['relaxations'];

        if ($candidates->isEmpty()) {
            return $this->emptyResponse($count, $targetCalories, $mealBudget, $resolvedMode, $relaxations);
        }

        $composition = null;
        $message = null;
        $pickedDishes = collect();
        $partial = false;

        if ($resolvedMode === SuggestMode::Compose) {
            $composed = $this->composer->compose(
                pool: $candidates,
                slot: $slot,
                size: $size,
                mode: $mode,
                count: $count,
                excludeIds: $excludeIds,
                recentIds: $recentIds,
                missingElements: $missingElements,
                mealBudget: $mealBudget,
                excludePlateSignatures: $excludePlateSignatures,
                softYhct: $softYhct,
            );

            if ($composed['ok'] && $composed['dishes'] !== [] && ! $composed['partial']) {
                $pickedDishes = collect($composed['dishes']);
                $composition = $composed['composition'];
                $partial = false;
            } elseif ($composed['ok'] && $composed['partial'] && $modeReq === SuggestMode::Compose) {
                // Explicit compose: show partial plate (missing slots) for transparency
                $pickedDishes = collect($composed['dishes']);
                $composition = $composed['composition'];
                $partial = true;
                $message = __('what_to_eat.compose_partial');
            } elseif ($composed['fallback_to_pick'] || ! $composed['ok'] || $composed['partial']) {
                // Auto (or soft fail): fall back to pick when structure pool insufficient
                $resolvedMode = SuggestMode::Pick;
                $message = __('what_to_eat.compose_fallback_pick');
            }

            // Reroll: pool hẹp → signature có thể trùng exclude list
            $sig = $composition['signature'] ?? null;
            if (is_string($sig) && $sig !== '' && in_array($sig, $excludePlateSignatures, true)) {
                $relaxations[] = 'plate_signature';
            }
        }

        if ($resolvedMode === SuggestMode::Pick || $pickedDishes->isEmpty()) {
            $scored = $candidates
                ->map(fn (Dish $dish) => [
                    'dish' => $dish,
                    'score' => $this->score($dish, $mode, $pref, $recentIds, $missingElements, $mealBudget),
                ])
                ->sortByDesc('score')
                ->values();

            $picked = $this->pickTopWithJitterDiversity($scored, $count);
            $pickedDishes = $picked->map(fn (array $row) => $row['dish']);
            $partial = $pickedDishes->count() < $count;
            $resolvedMode = SuggestMode::Pick;
        }

        $relaxations = array_values(array_unique($relaxations));
        if ($message === null && $relaxations !== []) {
            $message = $this->buildRelaxationMessage($relaxations, $culinaryRegion);
        } elseif ($message !== null && $relaxations !== []) {
            $relaxMsg = $this->buildRelaxationMessage($relaxations, $culinaryRegion);
            if ($relaxMsg !== null) {
                $message = $message.' '.$relaxMsg;
            }
        }

        $reason = $this->buildReason($slot, $size, $mode, $mealBudget, $resolvedMode);
        $cards = $pickedDishes->map(function (Dish $dish) use ($reason, $mode, $lat, $lng, $composition) {
            $dish->increment('suggest_count');
            $card = $dish->toSuggestionCard($reason);

            // Enrich reason from composition slot if any
            if ($composition !== null) {
                foreach ($composition['slots'] as $s) {
                    if (($s['dish_id'] ?? null) === $dish->id && ! empty($s['reasons'])) {
                        $card['reason'] = implode(' · ', $s['reasons']);
                        $card['slot_label'] = $s['label'] ?? null;
                        $card['slot_key'] = $s['key'] ?? null;
                        break;
                    }
                }
            }

            if ($mode === MealMode::DineOut) {
                $places = $this->placeMatcher->findPlaces($dish, $lat, $lng, 3);
                $card['places_count'] = count($places);
                $card['places_preview'] = array_slice($places, 0, 1);
            } else {
                $card['places_count'] = 0;
                $card['places_preview'] = [];
            }

            return $card;
        })->values()->all();

        // Embed full cards into composition for UI
        if ($composition !== null) {
            $byId = collect($cards)->keyBy('id');
            $composition['slots'] = array_map(function (array $s) use ($byId) {
                $id = $s['dish_id'] ?? null;
                $s['dish'] = $id ? ($byId->get($id) ?? null) : null;

                return $s;
            }, $composition['slots']);
        }

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
                    'suggest_mode' => $resolvedMode->value,
                    'culinary_region' => $culinaryRegion,
                    'composition' => $composition ? [
                        'template_id' => $composition['template_id'],
                        'signature' => $composition['signature'],
                    ] : null,
                    'exclude_plate_signatures' => $excludePlateSignatures,
                    'soft_yhct' => $softYhct,
                    'ruleset_version' => config('what_to_eat.ruleset_version'),
                ],
                'suggested_dish_ids' => array_column($cards, 'id'),
                'chosen_dish_id' => null,
                'outcome' => 'suggested',
                'created_at' => now(),
            ])->id;
        }

        return [
            'dishes' => $cards,
            'partial' => $partial,
            'total_available' => $candidates->count(),
            'count_requested' => $count,
            'log_id' => $logId,
            'target_calories' => $targetCalories,
            'meal_budget' => $mealBudget,
            'suggest_mode' => $resolvedMode->value,
            'composition' => $composition,
            'message' => $message,
            'relaxations' => $relaxations,
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

    private function resolveSuggestMode(
        SuggestMode $requested,
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        int $count,
    ): SuggestMode {
        if ($requested === SuggestMode::Pick || $requested === SuggestMode::Compose) {
            return $requested;
        }

        // Auto: breakfast multi-count → pick (pool thiếu canh/mặn/rau buổi sáng)
        if ($slot === MealSlot::Breakfast && $count >= 2) {
            return SuggestMode::Pick;
        }

        // Auto: compose for home main multi-slot; else pick
        if ($mode === MealMode::CookHome && $size === MealSize::Main && $count >= 2) {
            return SuggestMode::Compose;
        }

        if ($mode === MealMode::CookHome && $size === MealSize::Main && $count === 1) {
            return SuggestMode::Compose; // standalone_1
        }

        // Auto: dine_out + count≥2 → feast template (share_feast); pool thiếu → fallback pick
        if ($mode === MealMode::DineOut && $size === MealSize::Main && $count >= 2) {
            return SuggestMode::Compose;
        }

        return SuggestMode::Pick;
    }

    /**
     * Load candidates with ordered soft-relax. Never silent: each step appends a relaxation code.
     *
     * Order: strict → drop exclude_ids → drop meal_budget cap → drop culinary_region.
     *
     * @param  list<int>  $excludeIds
     * @return array{candidates: Collection<int, Dish>, relaxations: list<string>}
     */
    private function loadCandidates(
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        ?UserFoodPreference $pref,
        ?int $mealBudget,
        array $excludeIds,
        ?string $culinaryRegion,
        bool $skipSizeFilter = false,
    ): array {
        $relaxations = [];

        $candidates = $this->baseQuery($slot, $size, $mode, $pref, $mealBudget, $culinaryRegion, $skipSizeFilter)
            ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->get();

        if ($candidates->isEmpty() && $excludeIds !== []) {
            $candidates = $this->baseQuery($slot, $size, $mode, $pref, $mealBudget, $culinaryRegion, $skipSizeFilter)->get();
            if ($candidates->isNotEmpty()) {
                $relaxations[] = 'exclude_ids';
            }
        }

        if ($candidates->isEmpty() && $mealBudget !== null) {
            $candidates = $this->baseQuery($slot, $size, $mode, $pref, null, $culinaryRegion, $skipSizeFilter)
                ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->get();
            if ($candidates->isNotEmpty()) {
                $relaxations[] = 'meal_budget';
            }
            // If still empty with excludes, try budget drop without excludes
            if ($candidates->isEmpty() && $excludeIds !== []) {
                $candidates = $this->baseQuery($slot, $size, $mode, $pref, null, $culinaryRegion, $skipSizeFilter)->get();
                if ($candidates->isNotEmpty()) {
                    $relaxations[] = 'meal_budget';
                    $relaxations[] = 'exclude_ids';
                }
            }
        }

        // Region filter soft-relax if empty
        if ($candidates->isEmpty() && $culinaryRegion !== null) {
            $candidates = $this->baseQuery($slot, $size, $mode, $pref, $mealBudget, null, $skipSizeFilter)
                ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->get();
            if ($candidates->isNotEmpty()) {
                $relaxations[] = 'culinary_region';
            }
            if ($candidates->isEmpty()) {
                $candidates = $this->baseQuery($slot, $size, $mode, $pref, null, null, $skipSizeFilter)->get();
                if ($candidates->isNotEmpty()) {
                    $relaxations[] = 'culinary_region';
                    if ($mealBudget !== null) {
                        $relaxations[] = 'meal_budget';
                    }
                    if ($excludeIds !== []) {
                        $relaxations[] = 'exclude_ids';
                    }
                }
            }
        }

        return [
            'candidates' => $candidates,
            'relaxations' => array_values(array_unique($relaxations)),
        ];
    }

    /**
     * @param  list<string>  $relaxations
     */
    private function buildRelaxationMessage(array $relaxations, ?string $culinaryRegion): ?string
    {
        if ($relaxations === []) {
            return null;
        }

        $parts = [];
        if (in_array('culinary_region', $relaxations, true)) {
            $label = '';
            if ($culinaryRegion !== null) {
                try {
                    $label = CulinaryRegion::from($culinaryRegion)->label();
                } catch (\ValueError) {
                    $label = $culinaryRegion;
                }
            }
            $parts[] = $label !== ''
                ? __('what_to_eat.relax_region', ['region' => $label])
                : __('what_to_eat.relax_region_generic');
        }
        if (in_array('meal_budget', $relaxations, true)) {
            $parts[] = __('what_to_eat.relax_budget');
        }
        if (in_array('exclude_ids', $relaxations, true)) {
            $parts[] = __('what_to_eat.relax_exclude');
        }
        if (in_array('plate_signature', $relaxations, true)) {
            $parts[] = __('what_to_eat.reroll_same_plate');
        }

        $parts = array_values(array_filter($parts));

        return $parts === [] ? null : implode(' ', $parts);
    }

    /**
     * @param  list<string>  $relaxations
     * @return array{
     *     dishes: list<array<string, mixed>>,
     *     partial: bool,
     *     total_available: int,
     *     count_requested: int,
     *     log_id: null,
     *     target_calories: int|null,
     *     meal_budget: int|null,
     *     suggest_mode: string,
     *     composition: null,
     *     message: null,
     *     relaxations: list<string>
     * }
     */
    private function emptyResponse(
        int $count,
        ?int $targetCalories,
        ?int $mealBudget,
        SuggestMode $mode,
        array $relaxations = [],
    ): array {
        return [
            'dishes' => [],
            'partial' => true,
            'total_available' => 0,
            'count_requested' => $count,
            'log_id' => null,
            'target_calories' => $targetCalories,
            'meal_budget' => $mealBudget,
            'suggest_mode' => $mode->value,
            'composition' => null,
            'message' => null,
            'relaxations' => array_values(array_unique($relaxations)),
        ];
    }

    private function baseQuery(
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        ?UserFoodPreference $pref,
        ?int $mealBudget = null,
        ?string $culinaryRegion = null,
        bool $skipSizeFilter = false,
    ) {
        $q = Dish::query()
            ->published()
            ->forMealSlot($slot)
            ->forMealMode($mode);

        if (! $skipSizeFilter) {
            $q->forMealSize($size);
        }

        if ($culinaryRegion !== null) {
            $q->forCulinaryRegion($culinaryRegion);
        }

        $max = $mealBudget;
        if ($max === null && $pref?->max_calories_default) {
            $max = (int) $pref->max_calories_default;
        }
        if ($max !== null) {
            $softMax = (int) round($max * 1.25);
            $q->where(function ($builder) use ($softMax) {
                $builder->whereNull('calories_kcal')
                    ->orWhere('calories_kcal', '<=', $softMax);
            });
        }

        $flags = $pref?->diet_flags ?? [];
        if (in_array('vegetarian', $flags, true)) {
            // S02: prefer verified protein_source; never return known animal protein.
            // Avoid broad `%rau%` which matched meat soups (canh-rau-*-thit-bam).
            $q->where(function ($builder) {
                $builder->whereIn('protein_source', ['plant', 'none'])
                    ->orWhere(function ($nullProtein) {
                        $nullProtein->whereNull('protein_source')
                            ->where(function ($hint) {
                                $hint->where('name', 'like', '%chay%')
                                    ->orWhere('slug', 'like', '%chay%')
                                    ->orWhere('search_keywords', 'like', '%chay%')
                                    ->orWhere('search_keywords', 'like', '%vegetarian%')
                                    ->orWhereJsonContains('flavor_tags', 'chay')
                                    ->orWhereIn('slug', [
                                        'com-trang',
                                        'com-gao-lut',
                                        'xoi-trang',
                                        'sua-chua',
                                        'trai-cay-dia',
                                        'rau-muong-xao-toi',
                                        'cai-xao-toi',
                                        'su-su-xao-toi',
                                        'nam-xao-toi',
                                        'salad-dua-leo-ca-chua',
                                        'dau-phu-sot-ca',
                                        'dau-phu-chien',
                                        'dua-mon',
                                    ]);
                            });
                    });
            })->where(function ($builder) {
                $builder->whereNull('protein_source')
                    ->orWhereNotIn('protein_source', ['meat', 'seafood', 'egg', 'mixed']);
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
        // Jitter only after hard filters (already applied) — diversify pick band
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

    /**
     * Pick with soft diversity on protein_source / cooking_method inside top band.
     *
     * @param  Collection<int, array{dish: Dish, score: float}>  $scored
     * @return Collection<int, array{dish: Dish, score: float}>
     */
    private function pickTopWithJitterDiversity(Collection $scored, int $count): Collection
    {
        if ($scored->count() <= $count) {
            return $scored;
        }

        $bandSize = min($scored->count(), max($count * 4, $count + 6));
        $band = $scored->take($bandSize)->values();
        $picked = collect();
        $usedIds = [];
        $proteins = [];
        $fryCount = 0;

        // Greedy soft: prefer diverse protein, avoid second fry when alternatives exist
        $remaining = $band->shuffle()->values();
        while ($picked->count() < $count && $remaining->isNotEmpty()) {
            $bestIdx = 0;
            $bestAdj = -INF;
            foreach ($remaining as $i => $row) {
                /** @var Dish $dish */
                $dish = $row['dish'];
                if (in_array($dish->id, $usedIds, true)) {
                    continue;
                }
                $adj = (float) $row['score'];
                $ps = $dish->protein_source?->value;
                if ($ps !== null && in_array($ps, $proteins, true) && ! in_array($ps, ['plant', 'none'], true)) {
                    $adj -= 12;
                } elseif ($ps !== null && $proteins !== [] && ! in_array($ps, $proteins, true)) {
                    $adj += 5;
                }
                if ($dish->cooking_method?->value === 'fry' && $fryCount >= 1) {
                    $adj -= 18;
                }
                if ($adj > $bestAdj) {
                    $bestAdj = $adj;
                    $bestIdx = $i;
                }
            }
            $chosen = $remaining->get($bestIdx);
            if ($chosen === null) {
                break;
            }
            $picked->push($chosen);
            $dish = $chosen['dish'];
            $usedIds[] = $dish->id;
            if ($dish->protein_source !== null) {
                $proteins[] = $dish->protein_source->value;
            }
            if ($dish->cooking_method?->value === 'fry') {
                $fryCount++;
            }
            $remaining = $remaining->reject(fn ($r, $i) => $i === $bestIdx)->values();
        }

        return $picked->values();
    }

    private function buildReason(
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        ?int $mealBudget = null,
        ?SuggestMode $suggestMode = null,
    ): string {
        $base = __('what_to_eat.reason_template', [
            'slot' => $slot->label(),
            'size' => $size->label(),
            'mode' => $mode->label(),
        ]);

        if ($mealBudget !== null) {
            $base .= ' · '.__('what_to_eat.reason_budget', ['kcal' => $mealBudget]);
        }

        if ($suggestMode === SuggestMode::Compose) {
            $base .= ' · '.__('what_to_eat.reason_compose');
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
