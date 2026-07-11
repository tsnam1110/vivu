<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use App\Enums\ContributionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContributeDishRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(ContributionType::class)],
            'payload' => ['required', 'array'],
            'payload.ingredients' => ['nullable', 'array', 'max:40'],
            'payload.ingredients.*.name' => ['required_with:payload.ingredients', 'string', 'max:120'],
            'payload.ingredients.*.amount' => ['nullable', 'string', 'max:80'],
            'payload.steps' => ['nullable', 'array', 'max:40'],
            'payload.steps.*' => ['string', 'max:500'],
            'payload.cook_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'payload.servings' => ['nullable', 'integer', 'min:1', 'max:50'],
            'payload.difficulty' => ['nullable', 'string', Rule::in(['easy', 'medium', 'hard'])],
            'payload.kcal_per_serving' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'payload.serving_grams' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'payload.serving_size' => ['nullable', 'string', 'max:120'],
            'payload.body' => ['nullable', 'string', 'max:2000'],
            'payload.title' => ['nullable', 'string', 'max:150'],
            'payload.element' => ['nullable', 'string', Rule::in(['wood', 'fire', 'earth', 'metal', 'water'])],
            'payload.rationale' => ['nullable', 'string', 'max:500'],
        ];
    }
}
