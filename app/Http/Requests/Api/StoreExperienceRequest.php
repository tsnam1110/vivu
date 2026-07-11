<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ExperienceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('author_rating') === '' || $this->input('author_rating') === null) {
            $this->merge(['author_rating' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'content' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['sometimes', 'array', 'max:10'],
            'new_tags.*' => ['string', 'max:80'],
            'place_name' => ['nullable', 'string', 'max:180'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'author_rating' => ['nullable', 'integer', 'between:1,10'],
            'status' => ['sometimes', Rule::enum(ExperienceStatus::class)],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
            'cover_index' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
