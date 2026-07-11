<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CookingMethod;
use App\Enums\CulinaryRegion;
use App\Enums\DishRole;
use App\Enums\DishStatus;
use App\Enums\FiveElement;
use App\Enums\MealSlot;
use App\Enums\ProteinSource;
use App\Enums\ThermalNature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $slugRule = Rule::unique('dishes', 'slug');
        if ($this->route('dish')) {
            $slugRule = $slugRule->ignore($this->route('dish')->id);
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', $slugRule],
            'emoji' => ['nullable', 'string', 'max:16'],
            'summary' => ['nullable', 'string', 'max:500'],
            'meal_slots' => ['required', 'array', 'min:1'],
            'meal_slots.*' => ['string', Rule::enum(MealSlot::class)],
            'supports_light' => ['boolean'],
            'supports_main' => ['boolean'],
            'supports_dine_out' => ['boolean'],
            'supports_cook_home' => ['boolean'],
            'dish_role' => ['nullable', 'string', Rule::enum(DishRole::class)],
            'culinary_regions' => ['nullable', 'array'],
            'culinary_regions.*' => ['string', Rule::enum(CulinaryRegion::class)],
            'five_element' => ['nullable', 'string', Rule::enum(FiveElement::class)],
            'thermal_nature' => ['nullable', 'string', Rule::enum(ThermalNature::class)],
            'protein_source' => ['nullable', 'string', Rule::enum(ProteinSource::class)],
            'cooking_method' => ['nullable', 'string', Rule::enum(CookingMethod::class)],
            'flavor_tags' => ['nullable', 'array'],
            'flavor_tags.*' => ['string', 'max:32'],
            'calories_kcal' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'serving_grams' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'cook_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'ingredients' => ['nullable', 'array'],
            'steps' => ['nullable', 'array'],
            'benefits' => ['nullable', 'string', 'max:5000'],
            'harms' => ['nullable', 'string', 'max:5000'],
            'advice' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'search_keywords' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::enum(DishStatus::class)],
        ];
    }
}
