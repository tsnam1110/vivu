<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;
use App\Models\Dish;
use App\Services\DailyCalorieEstimator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuggestWhatToEatRequest extends FormRequest
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
            'meal_slot' => ['required', 'string', Rule::enum(MealSlot::class)],
            'meal_size' => ['required', 'string', Rule::enum(MealSize::class)],
            'meal_mode' => ['required', 'string', Rule::enum(MealMode::class)],
            'count' => ['nullable', 'integer', 'min:'.Dish::COUNT_MIN, 'max:'.Dish::COUNT_MAX],
            'exclude_ids' => ['nullable', 'array', 'max:50'],
            'exclude_ids.*' => ['integer', 'min:1'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'target_calories' => [
                'nullable',
                'integer',
                'min:'.DailyCalorieEstimator::MIN_DAILY,
                'max:'.DailyCalorieEstimator::MAX_DAILY,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'meal_slot.required' => __('what_to_eat.validation_slot'),
            'meal_size.required' => __('what_to_eat.validation_size'),
            'meal_mode.required' => __('what_to_eat.validation_mode'),
            'count.min' => __('what_to_eat.validation_count'),
            'count.max' => __('what_to_eat.validation_count'),
        ];
    }

    public function count(): int
    {
        return (int) ($this->validated('count') ?? Dish::COUNT_DEFAULT);
    }

    /**
     * @return list<int>
     */
    public function excludeIds(): array
    {
        /** @var list<int>|null $ids */
        $ids = $this->validated('exclude_ids');

        return array_values($ids ?? []);
    }

    public function targetCalories(): ?int
    {
        $v = $this->validated('target_calories');

        return $v !== null ? (int) $v : null;
    }
}
