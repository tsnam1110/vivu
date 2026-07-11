<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CookingMethod;
use App\Enums\CulinaryRegion;
use App\Enums\DishRole;
use App\Enums\DishStatus;
use App\Enums\FiveElement;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Enums\ProteinSource;
use App\Enums\ThermalNature;
use Database\Factories\DishFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Dish extends Model
{
    /** @use HasFactory<DishFactory> */
    use HasFactory, SoftDeletes;

    public const COUNT_MIN = 1;

    public const COUNT_MAX = 5;

    public const COUNT_DEFAULT = 3;

    /** Khối lượng tối thiểu (g) khi người dùng chỉnh khẩu phần. */
    public const PORTION_GRAMS_MIN = 10;

    /** Khối lượng tối đa (g) khi người dùng chỉnh khẩu phần. */
    public const PORTION_GRAMS_MAX = 2000;

    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'summary',
        'meal_slots',
        'supports_light',
        'supports_main',
        'supports_dine_out',
        'supports_cook_home',
        'dish_role',
        'culinary_regions',
        'five_element',
        'thermal_nature',
        'protein_source',
        'cooking_method',
        'flavor_tags',
        'calories_kcal',
        'serving_grams',
        'cook_minutes',
        'ingredients',
        'steps',
        'benefits',
        'harms',
        'advice',
        'notes',
        'search_keywords',
        'facts_meta',
        'status',
        'source',
        'created_by',
        'suggest_count',
    ];

    protected function casts(): array
    {
        return [
            'meal_slots' => 'array',
            'supports_light' => 'boolean',
            'supports_main' => 'boolean',
            'supports_dine_out' => 'boolean',
            'supports_cook_home' => 'boolean',
            'dish_role' => DishRole::class,
            'culinary_regions' => 'array',
            'five_element' => FiveElement::class,
            'thermal_nature' => ThermalNature::class,
            'protein_source' => ProteinSource::class,
            'cooking_method' => CookingMethod::class,
            'flavor_tags' => 'array',
            'calories_kcal' => 'integer',
            'serving_grams' => 'integer',
            'cook_minutes' => 'integer',
            'ingredients' => 'array',
            'steps' => 'array',
            'facts_meta' => 'array',
            'status' => DishStatus::class,
            'suggest_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dish $dish): void {
            if (blank($dish->slug)) {
                $dish->slug = static::uniqueSlugFromName($dish->name);
            }
        });
    }

    public static function uniqueSlugFromName(string $name): string
    {
        $base = Str::slug($name) ?: 'mon-an';
        $slug = $base;
        $i = 1;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(DishContribution::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', DishStatus::Published);
    }

    public function scopeForMealSlot(Builder $query, MealSlot|string $slot): Builder
    {
        $value = $slot instanceof MealSlot ? $slot->value : $slot;

        return $query->whereJsonContains('meal_slots', $value);
    }

    public function scopeForMealSize(Builder $query, MealSize|string $size): Builder
    {
        $value = $size instanceof MealSize ? $size : MealSize::from($size);

        return match ($value) {
            MealSize::Light => $query->where('supports_light', true),
            MealSize::Main => $query->where('supports_main', true),
        };
    }

    public function scopeForMealMode(Builder $query, MealMode|string $mode): Builder
    {
        $value = $mode instanceof MealMode ? $mode : MealMode::from($mode);

        return match ($value) {
            MealMode::DineOut => $query->where('supports_dine_out', true),
            MealMode::CookHome => $query->where('supports_cook_home', true),
        };
    }

    public function scopeForCulinaryRegion(Builder $query, CulinaryRegion|string $region): Builder
    {
        $value = $region instanceof CulinaryRegion ? $region->value : $region;

        return $query->whereJsonContains('culinary_regions', $value);
    }

    /**
     * @return list<string>
     */
    public function culinaryRegionLabels(): array
    {
        return collect($this->culinary_regions ?? [])
            ->map(fn (string $r) => CulinaryRegion::tryFrom($r)?->label() ?? $r)
            ->values()
            ->all();
    }

    public function hasRecipe(): bool
    {
        return ! empty($this->steps) || ! empty($this->ingredients);
    }

    public function hasCalorieBasis(): bool
    {
        return $this->calories_kcal !== null
            && $this->calories_kcal > 0
            && $this->serving_grams !== null
            && $this->serving_grams > 0;
    }

    /**
     * Quy đổi kcal theo khối lượng (g), làm tròn số nguyên.
     */
    public function caloriesForGrams(int $grams): ?int
    {
        if (! $this->hasCalorieBasis()) {
            return $this->calories_kcal;
        }

        $grams = max(self::PORTION_GRAMS_MIN, min(self::PORTION_GRAMS_MAX, $grams));

        return (int) max(0, (int) round($this->calories_kcal * ($grams / $this->serving_grams)));
    }

    /**
     * Quy đổi khối lượng (g) từ mức kcal mong muốn.
     */
    public function gramsForCalories(int $kcal): ?int
    {
        if (! $this->hasCalorieBasis()) {
            return $this->serving_grams;
        }

        $kcal = max(0, $kcal);
        $grams = (int) round($this->serving_grams * ($kcal / $this->calories_kcal));

        return max(self::PORTION_GRAMS_MIN, min(self::PORTION_GRAMS_MAX, $grams));
    }

    public function kcalPer100g(): ?float
    {
        if (! $this->hasCalorieBasis()) {
            return null;
        }

        return round($this->calories_kcal * (100 / $this->serving_grams), 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function caloriePayload(): array
    {
        return [
            'calories_kcal' => $this->calories_kcal,
            'serving_grams' => $this->serving_grams,
            'kcal_per_100g' => $this->kcalPer100g(),
            'has_calorie_basis' => $this->hasCalorieBasis(),
            'portion_grams_min' => self::PORTION_GRAMS_MIN,
            'portion_grams_max' => self::PORTION_GRAMS_MAX,
            'calories_basis_label' => $this->hasCalorieBasis()
                ? __('what_to_eat.calories_basis', [
                    'kcal' => $this->calories_kcal,
                    'grams' => $this->serving_grams,
                ])
                : null,
            'calorie_source' => $this->calorieSourceSummary(),
        ];
    }

    /**
     * Provenance snapshot for UI (from facts_meta calories fact).
     *
     * @return array<string, mixed>|null
     */
    public function calorieSourceSummary(): ?array
    {
        $facts = is_array($this->facts_meta['facts'] ?? null) ? $this->facts_meta['facts'] : [];
        foreach ($facts as $fact) {
            if (! is_array($fact) || ($fact['field'] ?? '') !== 'calories_kcal') {
                continue;
            }

            return [
                'method' => isset($fact['method']) ? (string) $fact['method'] : null,
                'method_label' => match ((string) ($fact['method'] ?? '')) {
                    'fct_table' => __('what_to_eat.calorie_method_fct'),
                    'recipe_sum' => __('what_to_eat.calorie_method_recipe_sum'),
                    'label' => __('what_to_eat.calorie_method_label'),
                    'lab' => __('what_to_eat.calorie_method_lab'),
                    default => (string) ($fact['method'] ?? ''),
                },
                'source_title' => isset($fact['source_title']) ? (string) $fact['source_title'] : null,
                'portion_note' => isset($fact['portion_note']) ? (string) $fact['portion_note'] : null,
                'confidence' => isset($fact['confidence']) ? (string) $fact['confidence'] : null,
                'limitations' => isset($fact['limitations']) ? (string) $fact['limitations'] : null,
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSuggestionCard(string $reason): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'emoji' => $this->emoji ?: '🍽️',
            'summary' => $this->summary,
            'dish_role' => $this->dish_role?->value,
            'dish_role_label' => $this->dish_role?->label(),
            'culinary_regions' => $this->culinary_regions ?? [],
            'culinary_region_labels' => $this->culinaryRegionLabels(),
            'five_element' => $this->five_element?->value,
            'five_element_label' => $this->five_element?->label(),
            'five_element_emoji' => $this->five_element?->emoji(),
            'thermal_nature' => $this->thermal_nature?->value,
            'thermal_nature_label' => $this->thermal_nature?->label(),
            'cook_minutes' => $this->cook_minutes,
            'reason' => $reason,
            'field_completeness' => $this->fieldCompleteness(),
        ], $this->caloriePayload());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailPayload(): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'emoji' => $this->emoji ?: '🍽️',
            'summary' => $this->summary,
            'meal_slots' => $this->meal_slots ?? [],
            'meal_slot_labels' => collect($this->meal_slots ?? [])
                ->map(fn (string $s) => MealSlot::tryFrom($s)?->label() ?? $s)
                ->values()
                ->all(),
            'supports_light' => $this->supports_light,
            'supports_main' => $this->supports_main,
            'supports_dine_out' => $this->supports_dine_out,
            'supports_cook_home' => $this->supports_cook_home,
            'dish_role' => $this->dish_role?->value,
            'dish_role_label' => $this->dish_role?->label(),
            'culinary_regions' => $this->culinary_regions ?? [],
            'culinary_region_labels' => $this->culinaryRegionLabels(),
            'five_element' => $this->five_element?->value,
            'five_element_label' => $this->five_element?->label(),
            'five_element_emoji' => $this->five_element?->emoji(),
            'thermal_nature' => $this->thermal_nature?->value,
            'thermal_nature_label' => $this->thermal_nature?->label(),
            'protein_source' => $this->protein_source?->value,
            'cooking_method' => $this->cooking_method?->value,
            'flavor_tags' => $this->flavor_tags ?? [],
            'cook_minutes' => $this->cook_minutes,
            'ingredients' => $this->ingredients ?? [],
            'steps' => $this->steps ?? [],
            'benefits' => $this->benefits,
            'harms' => $this->harms,
            'advice' => $this->advice,
            'notes' => $this->notes,
            'has_recipe' => $this->hasRecipe(),
            'field_completeness' => $this->fieldCompleteness(),
            'unverified_labels' => $this->unverifiedFieldLabels(),
            'community' => $this->relationLoaded('contributions')
                ? $this->contributions
                    ->where('status', \App\Enums\ContributionStatus::Approved)
                    ->map(fn (DishContribution $c) => $c->toPublicArray())
                    ->values()
                    ->all()
                : [],
        ], $this->caloriePayload());
    }

    /**
     * Which knowledge fields are present (non-null) — for UI / data-gate.
     *
     * @return array<string, bool>
     */
    public function fieldCompleteness(): array
    {
        return [
            'dish_role' => $this->dish_role !== null,
            'culinary_regions' => ! empty($this->culinary_regions),
            'calories' => $this->hasCalorieBasis(),
            'five_element' => $this->five_element !== null,
            'thermal_nature' => $this->thermal_nature !== null,
            'recipe' => $this->hasRecipe(),
            'benefits' => filled($this->benefits),
            'harms' => filled($this->harms),
            'advice' => filled($this->advice),
        ];
    }

    /**
     * User-facing list of missing verified facts (invite contribution).
     *
     * @return list<string>
     */
    public function unverifiedFieldLabels(): array
    {
        $labels = [];
        $c = $this->fieldCompleteness();
        if (! $c['calories']) {
            $labels[] = __('what_to_eat.unverified_calories');
        }
        if (! $c['recipe']) {
            $labels[] = __('what_to_eat.unverified_recipe');
        }
        if (! $c['five_element']) {
            $labels[] = __('what_to_eat.unverified_element');
        }
        if (! $c['thermal_nature']) {
            $labels[] = __('what_to_eat.unverified_thermal');
        }
        if (! $c['culinary_regions']) {
            $labels[] = __('what_to_eat.unverified_region');
        }
        if (! $c['benefits']) {
            $labels[] = __('what_to_eat.unverified_benefits');
        }
        if (! $c['harms']) {
            $labels[] = __('what_to_eat.unverified_harms');
        }

        return $labels;
    }
}
