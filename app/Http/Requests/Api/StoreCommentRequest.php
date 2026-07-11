<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }
}
