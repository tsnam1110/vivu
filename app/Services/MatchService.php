<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;

class MatchService
{
    public const WEIGHT_PERSONALITY = 0.4;

    public const WEIGHT_INTERESTS = 0.4;

    public const WEIGHT_CITY = 0.05;

    public const THRESHOLD = 0.15;

    /**
     * @return list<array{user: User, match_score: float, shared_traits: list<string>}>
     */
    public function findMatches(User $user, int $limit = 20, ?string $traitFilter = null): array
    {
        $profile = $user->profile;
        if (! $profile || ! $profile->is_matchable) {
            return [];
        }

        $myPersonality = $profile->personality ?? [];
        $myInterests = $profile->interests ?? [];
        $myAll = $profile->allTraitSlugs();

        if ($myAll === []) {
            return [];
        }

        $candidates = UserProfile::query()
            ->with('user')
            ->where('user_id', '!=', $user->id)
            ->where('is_matchable', true)
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->get();

        $results = [];

        foreach ($candidates as $candidate) {
            /** @var UserProfile $candidate */
            if (! $candidate->user) {
                continue;
            }

            $theirPersonality = $candidate->personality ?? [];
            $theirInterests = $candidate->interests ?? [];
            $shared = array_values(array_intersect($myAll, $candidate->allTraitSlugs()));

            if ($traitFilter !== null && ! in_array($traitFilter, $shared, true) && ! in_array($traitFilter, $candidate->allTraitSlugs(), true)) {
                continue;
            }

            $score = $this->score(
                $myPersonality,
                $theirPersonality,
                $myInterests,
                $theirInterests,
                $profile->location_city,
                $candidate->location_city,
            );

            if ($score < self::THRESHOLD) {
                continue;
            }

            $results[] = [
                'user' => $candidate->user,
                'match_score' => round($score, 4),
                'shared_traits' => $shared,
            ];
        }

        usort($results, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * @param  list<string>  $aPersonality
     * @param  list<string>  $bPersonality
     * @param  list<string>  $aInterests
     * @param  list<string>  $bInterests
     */
    public function score(
        array $aPersonality,
        array $bPersonality,
        array $aInterests,
        array $bInterests,
        ?string $cityA = null,
        ?string $cityB = null,
    ): float {
        $p = $this->jaccard($aPersonality, $bPersonality);
        $i = $this->jaccard($aInterests, $bInterests);
        $cityBonus = ($cityA && $cityB && mb_strtolower($cityA) === mb_strtolower($cityB)) ? 1.0 : 0.0;

        // w3 (categories) reserved at 0.15 for future; redistribute remaining into personality/interests
        return self::WEIGHT_PERSONALITY * $p
            + self::WEIGHT_INTERESTS * $i
            + self::WEIGHT_CITY * $cityBonus
            + 0.15 * $this->jaccard(
                array_merge($aPersonality, $aInterests),
                array_merge($bPersonality, $bInterests),
            );
    }

    /**
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    public function jaccard(array $a, array $b): float
    {
        $a = array_values(array_unique($a));
        $b = array_values(array_unique($b));

        if ($a === [] && $b === []) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union === 0 ? 0.0 : $intersection / $union;
    }
}
