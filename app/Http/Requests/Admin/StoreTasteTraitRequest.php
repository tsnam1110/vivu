<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TraitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTasteTraitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TraitType::class)],
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
