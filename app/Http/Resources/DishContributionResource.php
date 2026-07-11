<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DishContribution */
class DishContributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dish_id' => $this->dish_id,
            'user_id' => $this->user_id,
            'type' => $this->type?->value ?? $this->type,
            'type_label' => $this->type?->label(),
            'payload' => $this->payload,
            'status' => $this->status?->value ?? $this->status,
            'is_canonical' => $this->is_canonical,
            'review_note' => $this->review_note,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'dish' => $this->whenLoaded('dish', fn () => $this->dish
                ? [
                    'id' => $this->dish->id,
                    'name' => $this->dish->name,
                    'slug' => $this->dish->slug,
                    'emoji' => $this->dish->emoji,
                ]
                : null),
            'user' => $this->whenLoaded('user', fn () => $this->user
                ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                ]
                : null),
        ];
    }
}
