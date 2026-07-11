<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $year = (int) now(config('app.timezone'))->year;

        return [
            'bio' => ['nullable', 'string', 'max:2000'],
            'personality' => ['sometimes', 'array'],
            'personality.*' => ['string', 'max:100'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['string', 'max:100'],
            'location_city' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'height_cm' => ['nullable', 'integer', 'min:80', 'max:250'],
            'gender' => ['nullable', 'string', Rule::enum(Gender::class)],
            'birth_year' => ['nullable', 'integer', 'min:1920', 'max:'.($year - 10)],
            'activity_level' => ['nullable', 'string', Rule::enum(ActivityLevel::class)],
            'is_matchable' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['weight_kg', 'height_cm', 'birth_year', 'gender', 'activity_level'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
