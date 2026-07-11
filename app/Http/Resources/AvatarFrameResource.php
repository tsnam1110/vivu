<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AvatarFrame */
class AvatarFrameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'effect_type' => $this->effect_type?->value ?? $this->effect_type,
            'effect_config' => $this->effect_config ?? [],
            'css_variables' => $this->cssVariables(),
            'effect_class' => $this->effectClass(),
            'is_premium' => $this->is_premium,
            'show_badge' => $this->show_badge,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
