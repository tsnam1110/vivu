<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSampleAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        $id = $this->route('sample_avatar')?->id ?? $this->route('sampleAvatar')?->id;

        return [
            'slug' => ['sometimes', 'string', 'max:60', 'alpha_dash', Rule::unique('sample_avatars', 'slug')->ignore($id)],
            'name' => ['sometimes', 'string', 'max:100'],
            'path' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
