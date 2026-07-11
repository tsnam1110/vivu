<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use App\Enums\MealMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFoodPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->isActive();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'diet_flags' => ['nullable', 'array', 'max:10'],
            'diet_flags.*' => ['string', Rule::in(['vegetarian', 'no_seafood', 'no_spicy'])],
            'disliked_dish_ids' => ['nullable', 'array', 'max:50'],
            'disliked_dish_ids.*' => ['integer', 'exists:dishes,id'],
            'preferred_elements' => ['nullable', 'array', 'max:5'],
            'preferred_elements.*' => ['string', Rule::in(['wood', 'fire', 'earth', 'metal', 'water'])],
            'default_meal_mode' => ['nullable', 'string', Rule::enum(MealMode::class)],
            'max_calories_default' => ['nullable', 'integer', 'min:50', 'max:3000'],
            'balance_elements' => ['nullable', 'boolean'],
        ];
    }
}
