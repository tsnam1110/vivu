<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ReactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ReactionType::class)],
        ];
    }
}
