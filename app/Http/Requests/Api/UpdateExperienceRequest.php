<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ExperienceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $experience = $this->route('experience');

        return $this->user('web')?->can('update', $experience) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:180'],
            'content' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'place_name' => ['nullable', 'string', 'max:180'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(ExperienceStatus::class)],
        ];
    }
}
