<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:2000'],
            'personality' => ['sometimes', 'array'],
            'personality.*' => ['string', 'max:100'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['string', 'max:100'],
            'location_city' => ['nullable', 'string', 'max:100'],
            'is_matchable' => ['sometimes', 'boolean'],
        ];
    }
}
