<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CookingMethod;
use App\Enums\DishRole;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Enums\ProteinSource;
use App\Models\Dish;
use Illuminate\Support\Collection;

/**
 * Ghép mâm theo template + dish_role (ruleset lớp B + soft A/E).
 * Random chỉ sau hard-pass (chọn trong band ứng viên hợp lệ).
 */
class MealComposer
{
    public function __construct(
        private readonly MealTemplateRegistry $templates,
    ) {}

    /**
     * @param  Collection<int, Dish>  $pool  Already filtered published candidates
     * @param  list<int>  $excludeIds
     * @param  list<int>  $recentIds
     * @param  list<string>  $missingElements
     * @return array{
     *     ok: bool,
     *     partial: bool,
     *     dishes: list<Dish>,
     *     composition: array<string, mixed>|null,
     *     explanations: list<array<string, mixed>>,
     *     fallback_to_pick: bool
     * }
     */
    public function compose(
        Collection $pool,
        MealSlot $slot,
        MealSize $size,
        MealMode $mode,
        int $count,
        array $excludeIds = [],
        array $recentIds = [],
        array $missingElements = [],
        ?int $mealBudget = null,
        array $excludePlateSignatures = [],
        bool $softYhct = false,
    ): array {
        $template = $this->templates->resolve($slot, $size, $mode, $count);
        if ($template === null) {
            return $this->emptyResult(fallback: true);
        }

        $available = $pool->filter(fn (Dish $d) => ! in_array($d->id, $excludeIds, true))->values();
        $withRole = $available->filter(fn (Dish $d) => $d->dish_role !== null);

        // Không có role trong pool → không compose được (tránh gán role giả)
        if ($withRole->isEmpty()) {
            return $this->emptyResult(fallback: true, explanations: [[
                'rule_id' => 'B01_template_roles',
                'layer' => 'structure',
                'severity' => 'hard',
                'status' => 'skipped_missing_data',
                'message' => __('what_to_eat.explain_no_roles'),
            ]]);
        }

        $maxAttempts = $excludePlateSignatures !== [] ? 6 : 1;
        $lastResult = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $built = $this->buildPlate(
                $template,
                $withRole,
                $mode,
                $mealBudget,
                $recentIds,
                $missingElements,
                $softYhct,
            );
            $lastResult = $built;

            if (! $built['ok']) {
                return $built;
            }

            $sig = $built['composition']['signature'] ?? '';
            if ($sig === '' || ! in_array($sig, $excludePlateSignatures, true)) {
                return $built;
            }
            // Signature excluded — retry with extra jitter (band shuffle already random)
        }

        // Hết attempt: trả mâm cuối (có thể trùng signature) — UI/message xử lý nới pool
        return $lastResult ?? $this->emptyResult(fallback: true);
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  Collection<int, Dish>  $withRole
     * @param  list<int>  $recentIds
     * @param  list<string>  $missingElements
     * @return array{
     *     ok: bool,
     *     partial: bool,
     *     dishes: list<Dish>,
     *     composition: array<string, mixed>|null,
     *     explanations: list<array<string, mixed>>,
     *     fallback_to_pick: bool
     * }
     */
    private function buildPlate(
        array $template,
        Collection $withRole,
        MealMode $mode,
        ?int $mealBudget,
        array $recentIds,
        array $missingElements,
        bool $softYhct,
    ): array {
        $slotsOut = [];
        $usedIds = [];
        $explanations = [];
        $dishes = [];

        foreach ($template['slots'] as $slotDef) {
            $role = $slotDef['role'];
            $fallbackRoles = $template['fallback_roles'] ?? [];
            $rolesToTry = array_values(array_unique(array_merge([$role], $fallbackRoles)));

            $picked = null;
            $pickedRole = $role;
            foreach ($rolesToTry as $tryRole) {
                $candidates = $withRole
                    ->filter(fn (Dish $d) => $d->dish_role?->value === $tryRole)
                    ->filter(fn (Dish $d) => ! in_array($d->id, $usedIds, true))
                    ->filter(fn (Dish $d) => $d->dish_role !== DishRole::ShareFeast || $mode === MealMode::DineOut)
                    ->values();

                if ($candidates->isEmpty()) {
                    continue;
                }

                $scored = $candidates->map(function (Dish $dish) use (
                    $slotDef,
                    $mealBudget,
                    $recentIds,
                    $missingElements,
                    $dishes,
                ) {
                    return [
                        'dish' => $dish,
                        'score' => $this->scoreCandidate(
                            $dish,
                            $slotDef,
                            $mealBudget,
                            $recentIds,
                            $missingElements,
                            $dishes,
                        ),
                    ];
                })->sortByDesc('score')->values();

                // Jitter only inside hard-pass band
                $band = $scored->take(min(5, $scored->count()));
                $picked = $band->shuffle()->first()['dish'] ?? null;
                $pickedRole = $tryRole;
                break;
            }

            if ($picked === null) {
                if ($slotDef['required']) {
                    $explanations[] = [
                        'rule_id' => 'B01_template_roles',
                        'layer' => 'structure',
                        'severity' => 'hard',
                        'status' => 'fail',
                        'message' => __('what_to_eat.explain_missing_role', ['role' => $slotDef['label']]),
                        'fields_used' => ['dish_role'],
                    ];
                }
                $slotsOut[] = [
                    'key' => $slotDef['key'],
                    'role' => $role,
                    'label' => $slotDef['label'],
                    'required' => $slotDef['required'],
                    'dish' => null,
                    'reasons' => [__('what_to_eat.explain_missing_role', ['role' => $slotDef['label']])],
                ];

                continue;
            }

            $usedIds[] = $picked->id;
            $dishes[] = $picked;
            $reasons = $this->slotReasons($picked, $slotDef, $mealBudget);
            $slotsOut[] = [
                'key' => $slotDef['key'],
                'role' => $pickedRole,
                'label' => $slotDef['label'],
                'required' => $slotDef['required'],
                'dish' => $picked,
                'reasons' => $reasons,
            ];
        }

        $requiredCount = collect($template['slots'])->where('required', true)->count();
        $filledRequired = collect($slotsOut)->filter(fn ($s) => $s['required'] && $s['dish'] !== null)->count();
        $partial = $filledRequired < $requiredCount;
        $ok = $filledRequired > 0;

        if (! $ok) {
            return $this->emptyResult(fallback: true, explanations: $explanations);
        }

        // Soft plate rules
        $plateSoft = $this->evaluatePlateSoft($dishes, $mealBudget, $template, $softYhct);
        $explanations = array_merge($explanations, $plateSoft['explanations']);

        if ($filledRequired === $requiredCount) {
            $explanations[] = [
                'rule_id' => 'B01_template_roles',
                'layer' => 'structure',
                'severity' => 'hard',
                'status' => 'pass',
                'message' => __('what_to_eat.explain_structure_ok', ['template' => $template['summary']]),
                'fields_used' => ['dish_role'],
            ];
        }

        $kcalSum = collect($dishes)->sum(fn (Dish $d) => (int) ($d->calories_kcal ?? 0));
        $allHaveKcal = collect($dishes)->every(fn (Dish $d) => $d->calories_kcal !== null);
        $signature = collect($dishes)->pluck('id')->sort()->values()->implode('-');

        $composition = [
            'template_id' => $template['id'],
            'template_label' => $template['label'],
            'template_summary' => $template['summary'],
            'slots' => array_map(function (array $s) {
                /** @var Dish|null $dish */
                $dish = $s['dish'];

                return [
                    'key' => $s['key'],
                    'role' => $s['role'],
                    'label' => $s['label'],
                    'required' => $s['required'],
                    'dish_id' => $dish?->id,
                    'reasons' => $s['reasons'],
                ];
            }, $slotsOut),
            'implicit' => $template['implicit'],
            'totals' => [
                'kcal' => $allHaveKcal ? $kcalSum : null,
                'meal_budget' => $mealBudget,
                'within_band' => $plateSoft['within_band'],
                'all_have_kcal' => $allHaveKcal,
            ],
            'plate_reasons' => $plateSoft['plate_reasons'],
            'explanations' => $explanations,
            'signature' => $signature,
            'ruleset_version' => config('what_to_eat.ruleset_version'),
        ];

        return [
            'ok' => true,
            'partial' => $partial,
            'dishes' => $dishes,
            'composition' => $composition,
            'explanations' => $explanations,
            'fallback_to_pick' => false,
        ];
    }

    /**
     * @param  list<Dish>  $already
     * @param  list<int>  $recentIds
     * @param  list<string>  $missingElements
     * @param  array{key: string, role: string, label: string, required: bool, calorie_share: float}  $slotDef
     */
    private function scoreCandidate(
        Dish $dish,
        array $slotDef,
        ?int $mealBudget,
        array $recentIds,
        array $missingElements,
        array $already,
    ): float {
        $score = 50.0;

        if ($dish->hasRecipe()) {
            $score += 12;
        }

        if ($mealBudget !== null && $dish->calories_kcal !== null && $mealBudget > 0) {
            $target = (int) round($mealBudget * $slotDef['calorie_share']);
            if ($target > 0) {
                $ratio = abs($dish->calories_kcal - $target) / $target;
                $score += max(0, 18 - ($ratio * 25));
            }
        } elseif ($dish->calories_kcal !== null) {
            $score += 3;
        }

        // Soft diversity: avoid same animal protein_source; reward different source
        if ($dish->protein_source !== null) {
            $animalDup = false;
            $hasKnownProtein = false;
            foreach ($already as $prev) {
                if ($prev->protein_source === null) {
                    continue;
                }
                $hasKnownProtein = true;
                if ($prev->protein_source === $dish->protein_source
                    && $dish->protein_source !== ProteinSource::None
                    && $dish->protein_source !== ProteinSource::Plant) {
                    $animalDup = true;
                    $score -= 14;
                } elseif ($prev->protein_source !== $dish->protein_source) {
                    $score += 6; // reward diversity when both known
                }
            }
            if ($hasKnownProtein && ! $animalDup
                && ! in_array($dish->protein_source, [ProteinSource::None, ProteinSource::Plant], true)) {
                $score += 2;
            }
        }

        // Soft: avoid second fry (null cooking_method → skip)
        if ($dish->cooking_method === CookingMethod::Fry) {
            foreach ($already as $prev) {
                if ($prev->cooking_method === CookingMethod::Fry) {
                    $score -= 22;
                }
            }
        } elseif ($dish->cooking_method !== null) {
            foreach ($already as $prev) {
                if ($prev->cooking_method === CookingMethod::Fry) {
                    $score += 4; // prefer non-fry after a fry already picked
                }
            }
        }

        if ($dish->five_element !== null && $missingElements !== []
            && in_array($dish->five_element->value, $missingElements, true)) {
            $score += 8;
        }

        if (in_array($dish->id, $recentIds, true)) {
            $score -= 15;
        }

        $score += min(8.0, log(1 + $dish->suggest_count) * 1.5);
        // tiny jitter inside candidate scoring (band still re-shuffled)
        $score += random_int(0, 20) / 10;

        return $score;
    }

    /**
     * @param  array{key: string, role: string, label: string, required: bool, calorie_share: float}  $slotDef
     * @return list<string>
     */
    private function slotReasons(Dish $dish, array $slotDef, ?int $mealBudget): array
    {
        $reasons = [__('what_to_eat.reason_slot_role', ['role' => $slotDef['label']])];
        if ($dish->calories_kcal !== null && $mealBudget !== null) {
            $reasons[] = __('what_to_eat.reason_slot_kcal', ['kcal' => $dish->calories_kcal]);
        }
        if ($dish->culinaryRegionLabels() !== []) {
            $reasons[] = implode(', ', $dish->culinaryRegionLabels());
        }

        return $reasons;
    }

    /**
     * @param  list<Dish>  $dishes
     * @param  array<string, mixed>  $template
     * @return array{explanations: list<array<string, mixed>>, plate_reasons: list<string>, within_band: bool|null}
     */
    private function evaluatePlateSoft(array $dishes, ?int $mealBudget, array $template, bool $softYhct = false): array
    {
        $explanations = [];
        $plateReasons = [];
        $withinBand = null;

        $fryCount = collect($dishes)->filter(fn (Dish $d) => $d->cooking_method === CookingMethod::Fry)->count();
        if ($fryCount >= 2) {
            $explanations[] = [
                'rule_id' => 'A05_single_deep_fry',
                'layer' => 'nutrition',
                'severity' => 'soft',
                'status' => 'soft_fail',
                'message' => __('what_to_eat.explain_double_fry'),
                'fields_used' => ['cooking_method'],
            ];
            $plateReasons[] = __('what_to_eat.explain_double_fry');
        } elseif ($fryCount <= 1 && collect($dishes)->contains(fn (Dish $d) => $d->cooking_method !== null)) {
            $explanations[] = [
                'rule_id' => 'A05_single_deep_fry',
                'layer' => 'nutrition',
                'severity' => 'soft',
                'status' => 'pass',
                'message' => __('what_to_eat.explain_fry_ok'),
                'fields_used' => ['cooking_method'],
            ];
        }

        // Soft protein diversity (skip nulls)
        $proteinKnown = collect($dishes)->filter(fn (Dish $d) => $d->protein_source !== null);
        if ($proteinKnown->count() >= 2) {
            $animal = $proteinKnown->filter(fn (Dish $d) => ! in_array(
                $d->protein_source,
                [ProteinSource::None, ProteinSource::Plant],
                true,
            ));
            $dup = $animal->groupBy(fn (Dish $d) => $d->protein_source?->value)
                ->filter(fn ($g) => $g->count() >= 2)
                ->keys()
                ->first();
            if ($dup !== null) {
                $explanations[] = [
                    'rule_id' => 'E01_protein_diversity',
                    'layer' => 'diversity',
                    'severity' => 'soft',
                    'status' => 'soft_fail',
                    'message' => __('what_to_eat.explain_protein_dup', ['protein' => $dup]),
                    'fields_used' => ['protein_source'],
                ];
                $plateReasons[] = __('what_to_eat.explain_protein_dup', ['protein' => $dup]);
            } else {
                $explanations[] = [
                    'rule_id' => 'E01_protein_diversity',
                    'layer' => 'diversity',
                    'severity' => 'soft',
                    'status' => 'pass',
                    'message' => __('what_to_eat.explain_protein_ok'),
                    'fields_used' => ['protein_source'],
                ];
            }
        }

        $allKcal = collect($dishes)->every(fn (Dish $d) => $d->calories_kcal !== null);
        if ($allKcal && $mealBudget !== null && $mealBudget > 0) {
            $sum = collect($dishes)->sum(fn (Dish $d) => (int) $d->calories_kcal);
            $low = (int) round($mealBudget * 0.75);
            $high = (int) round($mealBudget * 1.15);
            $withinBand = $sum >= $low && $sum <= $high;
            $explanations[] = [
                'rule_id' => 'A02_plate_kcal_band',
                'layer' => 'nutrition',
                'severity' => 'soft',
                'status' => $withinBand ? 'pass' : 'soft_fail',
                'message' => $withinBand
                    ? __('what_to_eat.explain_kcal_band_ok', ['sum' => $sum, 'budget' => $mealBudget])
                    : __('what_to_eat.explain_kcal_band_off', ['sum' => $sum, 'budget' => $mealBudget]),
                'fields_used' => ['calories_kcal'],
            ];
            $plateReasons[] = $withinBand
                ? __('what_to_eat.explain_kcal_band_ok', ['sum' => $sum, 'budget' => $mealBudget])
                : __('what_to_eat.explain_kcal_band_off', ['sum' => $sum, 'budget' => $mealBudget]);
        } else {
            $explanations[] = [
                'rule_id' => 'A03_skip_null_kcal',
                'layer' => 'nutrition',
                'severity' => 'info',
                'status' => 'skipped_missing_data',
                'message' => __('what_to_eat.explain_kcal_incomplete'),
                'fields_used' => ['calories_kcal'],
            ];
        }

        // Thermal soft ONLY when user opt-in (balance_elements / soft YHCT) + all have thermal
        if ($softYhct) {
            $allThermal = collect($dishes)->every(fn (Dish $d) => $d->thermal_nature !== null);
            if ($allThermal && count($dishes) >= 2) {
                $hots = collect($dishes)->filter(fn (Dish $d) => in_array($d->thermal_nature?->value, ['hot', 'warm'], true))->count();
                if ($hots === count($dishes)) {
                    $explanations[] = [
                        'rule_id' => 'C01_no_all_hot',
                        'layer' => 'thermal',
                        'severity' => 'soft',
                        'status' => 'soft_fail',
                        'message' => __('what_to_eat.explain_all_hot'),
                        'fields_used' => ['thermal_nature'],
                    ];
                } else {
                    $explanations[] = [
                        'rule_id' => 'C01_no_all_hot',
                        'layer' => 'thermal',
                        'severity' => 'soft',
                        'status' => 'pass',
                        'message' => __('what_to_eat.explain_thermal_ok'),
                        'fields_used' => ['thermal_nature'],
                    ];
                }
            }
        }

        $plateReasons[] = $template['summary'];

        return [
            'explanations' => $explanations,
            'plate_reasons' => array_values(array_unique($plateReasons)),
            'within_band' => $withinBand,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $explanations
     * @return array{
     *     ok: bool,
     *     partial: bool,
     *     dishes: list<Dish>,
     *     composition: null,
     *     explanations: list<array<string, mixed>>,
     *     fallback_to_pick: bool
     * }
     */
    private function emptyResult(bool $fallback, array $explanations = []): array
    {
        return [
            'ok' => false,
            'partial' => true,
            'dishes' => [],
            'composition' => null,
            'explanations' => $explanations,
            'fallback_to_pick' => $fallback,
        ];
    }
}
