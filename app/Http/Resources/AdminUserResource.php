<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'status' => $this->status?->value,
            'avatar_url' => $this->avatarUrl(),
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
