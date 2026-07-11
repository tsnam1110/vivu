<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ContributionStatus;
use App\Enums\DishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ChooseWhatToEatRequest;
use App\Http\Requests\Web\ContributeDishRequest;
use App\Http\Requests\Web\SuggestWhatToEatRequest;
use App\Http\Requests\Web\UpdateFoodPreferenceRequest;
use App\Models\Dish;
use App\Services\DishContributionService;
use App\Services\WhatToEatPlaceMatcher;
use App\Services\WhatToEatSuggester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatToEatController extends Controller
{
    public function __construct(
        private readonly WhatToEatSuggester $suggester,
        private readonly DishContributionService $contributions,
        private readonly WhatToEatPlaceMatcher $placeMatcher,
    ) {}

    public function suggest(SuggestWhatToEatRequest $request): JsonResponse
    {
        $user = $request->user('web');
        $lat = $request->filled('lat') ? (float) $request->validated('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->validated('lng') : null;

        $result = $this->suggester->suggest(
            mealSlot: $request->validated('meal_slot'),
            mealSize: $request->validated('meal_size'),
            mealMode: $request->validated('meal_mode'),
            count: $request->count(),
            excludeIds: $request->excludeIds(),
            user: $user,
            lat: $lat,
            lng: $lng,
            targetCalories: $request->targetCalories(),
            suggestMode: $request->suggestMode(),
            culinaryRegion: $request->culinaryRegion(),
        );

        $catalogEmpty = \App\Models\Dish::query()->published()->count() === 0;

        $message = $result['message'] ?? null;
        if ($result['dishes'] === []) {
            $message = $catalogEmpty
                ? __('what_to_eat.empty_catalog')
                : __('what_to_eat.empty');
        } elseif ($result['partial'] && $message === null) {
            $message = __('what_to_eat.partial', ['count' => count($result['dishes'])]);
        }

        return response()->json([
            'data' => $result['dishes'],
            'meta' => [
                'partial' => $result['partial'],
                'total_available' => $result['total_available'],
                'count_requested' => $result['count_requested'],
                'log_id' => $result['log_id'],
                'target_calories' => $result['target_calories'],
                'meal_budget' => $result['meal_budget'],
                'catalog_empty' => $catalogEmpty,
                'ruleset_version' => config('what_to_eat.ruleset_version'),
                'suggest_mode' => $result['suggest_mode'],
                'composition' => $result['composition'],
                'message' => $message,
            ],
        ]);
    }

    public function show(Request $request, Dish $dish): JsonResponse
    {
        abort_unless($dish->status === DishStatus::Published, 404);

        $dish->load(['contributions' => function ($q) {
            $q->approved()->with('user')->orderByDesc('is_canonical')->orderByDesc('id');
        }]);

        $lat = $request->filled('lat') ? (float) $request->query('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->query('lng') : null;

        $places = $this->placeMatcher->findPlaces($dish, $lat, $lng, 5);

        return response()->json([
            'data' => array_merge($dish->toDetailPayload(), [
                'places' => $places,
            ]),
            'meta' => [
                'disclaimer' => __('what_to_eat.disclaimer'),
            ],
        ]);
    }

    public function contribute(ContributeDishRequest $request, Dish $dish): JsonResponse
    {
        abort_unless($dish->status === DishStatus::Published, 404);

        $user = $request->user('web');
        $contribution = $this->contributions->submit(
            $user,
            $dish,
            $request->validated('type'),
            $request->validated('payload'),
        );

        return response()->json([
            'data' => $contribution->toPublicArray(),
            'meta' => [
                'message' => __('what_to_eat.contribute_success'),
            ],
        ], 201);
    }

    public function choose(ChooseWhatToEatRequest $request): JsonResponse
    {
        $user = $request->user('web');
        $dish = Dish::query()->published()->findOrFail($request->validated('dish_id'));

        $log = $this->suggester->choose(
            $user,
            (int) $request->validated('log_id'),
            $dish,
        );

        return response()->json([
            'data' => [
                'log_id' => $log->id,
                'chosen_dish_id' => $log->chosen_dish_id,
                'outcome' => $log->outcome,
            ],
            'meta' => [
                'message' => __('what_to_eat.choose_success'),
            ],
        ]);
    }

    public function history(Request $request): View|JsonResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $items = $this->suggester->history($user, 40);

        if ($request->expectsJson()) {
            return response()->json(['data' => $items]);
        }

        return view('what-to-eat.history', [
            'items' => $items,
            'preference' => $this->suggester->preferenceFor($user),
        ]);
    }

    public function showPreferences(Request $request): JsonResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $pref = $this->suggester->preferenceFor($user);

        return response()->json([
            'data' => $pref ? [
                'diet_flags' => $pref->diet_flags ?? [],
                'disliked_dish_ids' => $pref->disliked_dish_ids ?? [],
                'preferred_elements' => $pref->preferred_elements ?? [],
                'default_meal_mode' => $pref->default_meal_mode,
                'max_calories_default' => $pref->max_calories_default,
                'balance_elements' => (bool) $pref->balance_elements,
            ] : [
                'diet_flags' => [],
                'disliked_dish_ids' => [],
                'preferred_elements' => [],
                'default_meal_mode' => null,
                'max_calories_default' => null,
                'balance_elements' => false,
            ],
        ]);
    }

    public function updatePreferences(UpdateFoodPreferenceRequest $request): JsonResponse
    {
        $user = $request->user('web');
        $pref = $this->suggester->upsertPreference($user, $request->validated());

        return response()->json([
            'data' => [
                'diet_flags' => $pref->diet_flags ?? [],
                'disliked_dish_ids' => $pref->disliked_dish_ids ?? [],
                'preferred_elements' => $pref->preferred_elements ?? [],
                'default_meal_mode' => $pref->default_meal_mode,
                'max_calories_default' => $pref->max_calories_default,
                'balance_elements' => (bool) $pref->balance_elements,
            ],
            'meta' => ['message' => __('what_to_eat.pref_saved')],
        ]);
    }
}
