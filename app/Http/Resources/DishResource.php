<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Dish */
class DishResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'emoji' => $this->emoji,
            'summary' => $this->summary,
            'meal_slots' => $this->meal_slots ?? [],
            'supports_light' => $this->supports_light,
            'supports_main' => $this->supports_main,
            'supports_dine_out' => $this->supports_dine_out,
            'supports_cook_home' => $this->supports_cook_home,
            'dish_role' => $this->dish_role?->value,
            'culinary_regions' => $this->culinary_regions ?? [],
            'culinary_region_labels' => $this->culinaryRegionLabels(),
            'five_element' => $this->five_element?->value,
            'thermal_nature' => $this->thermal_nature?->value,
            'protein_source' => $this->protein_source?->value,
            'cooking_method' => $this->cooking_method?->value,
            'flavor_tags' => $this->flavor_tags ?? [],
            'calories_kcal' => $this->calories_kcal,
            'serving_grams' => $this->serving_grams,
            'kcal_per_100g' => $this->kcalPer100g(),
            'cook_minutes' => $this->cook_minutes,
            'ingredients' => $this->ingredients,
            'steps' => $this->steps,
            'benefits' => $this->benefits,
            'harms' => $this->harms,
            'advice' => $this->advice,
            'notes' => $this->notes,
            'search_keywords' => $this->search_keywords,
            'facts_meta' => $this->facts_meta,
            'field_completeness' => $this->fieldCompleteness(),
            'status' => $this->status?->value ?? $this->status,
            'source' => $this->source,
            'suggest_count' => $this->suggest_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
