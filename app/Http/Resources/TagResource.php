<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tag */
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'usage_count' => $this->usage_count,
            'status' => $this->status?->value ?? $this->status,
            'created_by' => $this->created_by,
            'category' => $this->whenLoaded('category', fn () => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'icon' => $this->category->icon,
                ]
                : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator
                ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'username' => $this->creator->username,
                ]
                : null),
        ];
    }
}
