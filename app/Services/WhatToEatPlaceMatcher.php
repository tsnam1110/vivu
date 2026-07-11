<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Dish;
use App\Models\Experience;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WhatToEatPlaceMatcher
{
    public function __construct(
        private readonly ExperienceService $experienceService,
    ) {}

    /**
     * Soft-match published experiences to a dish (eat-out).
     *
     * @return list<array<string, mixed>>
     */
    public function findPlaces(Dish $dish, ?float $lat = null, ?float $lng = null, int $limit = 5): array
    {
        $tokens = $this->tokensFor($dish);
        if ($tokens === []) {
            return [];
        }

        $query = Experience::query()
            ->published()
            ->with(['category', 'user'])
            ->where(function ($q) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%'.$token.'%';
                    $q->orWhere('title', 'like', $like)
                        ->orWhere('place_name', 'like', $like)
                        ->orWhere('content', 'like', $like);
                }
            });

        if ($lat !== null && $lng !== null) {
            $query->nearby($lat, $lng, 15);
        }

        /** @var Collection<int, Experience> $rows */
        $rows = $query->orderByDesc('published_at')->limit(40)->get();

        if ($lat !== null && $lng !== null) {
            $rows = $rows
                ->map(function (Experience $exp) use ($lat, $lng) {
                    $dist = ($exp->latitude !== null && $exp->longitude !== null)
                        ? $this->experienceService->haversineKm($lat, $lng, (float) $exp->latitude, (float) $exp->longitude)
                        : null;

                    return ['exp' => $exp, 'distance_km' => $dist];
                })
                ->sortBy(fn (array $r) => $r['distance_km'] ?? 9999)
                ->take($limit)
                ->values();
        } else {
            $rows = $rows->take($limit)->map(fn (Experience $exp) => [
                'exp' => $exp,
                'distance_km' => null,
            ]);
        }

        return $rows->map(function (array $row) {
            /** @var Experience $exp */
            $exp = $row['exp'];

            return [
                'id' => $exp->id,
                'title' => $exp->title,
                'slug' => $exp->slug,
                'place_name' => $exp->place_name,
                'address' => $exp->address,
                'url' => route('experiences.show', $exp->slug),
                'rating_avg' => $exp->rating_avg,
                'distance_km' => $row['distance_km'] !== null
                    ? round((float) $row['distance_km'], 1)
                    : null,
                'author' => $exp->user?->name,
            ];
        })->all();
    }

    /**
     * @return list<string>
     */
    private function tokensFor(Dish $dish): array
    {
        $raw = trim(($dish->search_keywords ?? '').' '.$dish->name);
        $parts = preg_split('/[\s,;|\/]+/u', Str::lower($raw)) ?: [];

        $tokens = collect($parts)
            ->map(fn (string $t) => trim($t))
            ->filter(fn (string $t) => mb_strlen($t) >= 2)
            ->unique()
            ->take(8)
            ->values()
            ->all();

        return $tokens;
    }
}
