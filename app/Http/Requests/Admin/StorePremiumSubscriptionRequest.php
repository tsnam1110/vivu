<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePremiumSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required_without:username', 'nullable', 'integer', 'exists:users,id'],
            'username' => ['required_without:user_id', 'nullable', 'string', 'exists:users,username'],
            'days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'lifetime' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('lifetime') && $this->filled('days')) {
                // lifetime wins — ok
            }
            if (! $this->boolean('lifetime') && ! $this->filled('days') && ! $this->filled('ends_at')) {
                $validator->errors()->add('days', 'Cần số ngày, lifetime, hoặc ends_at.');
            }
        });
    }
}
