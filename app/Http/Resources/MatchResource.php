<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array{user: \App\Models\User, match_score: float, shared_traits: list<string>} $data */
        $data = $this->resource;

        return [
            'id' => $data['user']->id,
            'name' => $data['user']->name,
            'username' => $data['user']->username,
            'avatar_url' => $data['user']->avatarUrl(),
            'match_score' => $data['match_score'],
            'shared_traits' => $data['shared_traits'],
        ];
    }
}
