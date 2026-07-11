<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\UserProfile */
class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bio' => $this->bio,
            'personality' => $this->personality ?? [],
            'interests' => $this->interests ?? [],
            'location_city' => $this->location_city,
            'is_matchable' => $this->is_matchable,
        ];
    }
}
