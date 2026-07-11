<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TagStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TagStatus::class)],
        ];
    }
}
