<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PremiumSubscription */
class PremiumSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'premium_expires_at' => $this->user->premium_expires_at?->toIso8601String(),
                'has_active_premium' => $this->user->hasActivePremium(),
            ]),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_lifetime' => $this->ends_at === null,
            'status' => $this->status?->value,
            'source' => $this->source?->value,
            'notes' => $this->notes,
            'granted_by_admin_id' => $this->granted_by_admin_id,
            'granted_by' => $this->whenLoaded('grantedByAdmin', fn () => $this->grantedByAdmin ? [
                'id' => $this->grantedByAdmin->id,
                'name' => $this->grantedByAdmin->name,
            ] : null),
            'is_currently_valid' => $this->isCurrentlyValid(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
