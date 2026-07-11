<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AvatarEffectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvatarFrameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        $id = $this->route('avatar_frame')?->id ?? $this->route('avatarFrame')?->id;

        return [
            'slug' => ['sometimes', 'string', 'max:60', 'alpha_dash', Rule::unique('avatar_frames', 'slug')->ignore($id)],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'effect_type' => ['sometimes', Rule::enum(AvatarEffectType::class)],
            'effect_config' => ['nullable', 'array'],
            'effect_config.colors' => ['nullable', 'array', 'max:6'],
            'effect_config.colors.*' => ['string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'effect_config.thickness' => ['nullable', 'integer', 'min:1', 'max:8'],
            'effect_config.speed_ms' => ['nullable', 'integer', 'min:800', 'max:12000'],
            'effect_config.intensity' => ['nullable', 'numeric', 'min:0.1', 'max:1'],
            'is_premium' => ['sometimes', 'boolean'],
            'show_badge' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
