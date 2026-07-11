<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TraitType;
use App\Models\TasteTrait;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    /**
     * @param  array{bio?: string|null, personality?: list<string>, interests?: list<string>, location_city?: string|null, is_matchable?: bool}  $data
     */
    public function updateProfile(User $user, array $data): UserProfile
    {
        $profile = $user->profile ?? UserProfile::query()->create([
            'user_id' => $user->id,
            'personality' => [],
            'interests' => [],
        ]);

        if (isset($data['personality'])) {
            $this->assertValidTraits($data['personality'], TraitType::Personality);
        }
        if (isset($data['interests'])) {
            $this->assertValidTraits($data['interests'], TraitType::Interest);
        }

        $profile->fill(array_filter([
            'bio' => $data['bio'] ?? $profile->bio,
            'personality' => $data['personality'] ?? $profile->personality,
            'interests' => $data['interests'] ?? $profile->interests,
            'location_city' => array_key_exists('location_city', $data) ? $data['location_city'] : $profile->location_city,
            'is_matchable' => array_key_exists('is_matchable', $data) ? $data['is_matchable'] : $profile->is_matchable,
        ], fn ($v) => $v !== null || array_key_exists('location_city', $data)));

        // handle nullable fields more carefully
        if (array_key_exists('bio', $data)) {
            $profile->bio = $data['bio'];
        }
        if (array_key_exists('personality', $data)) {
            $profile->personality = $data['personality'];
        }
        if (array_key_exists('interests', $data)) {
            $profile->interests = $data['interests'];
        }
        if (array_key_exists('location_city', $data)) {
            $profile->location_city = $data['location_city'];
        }
        if (array_key_exists('is_matchable', $data)) {
            $profile->is_matchable = (bool) $data['is_matchable'];
        }

        $profile->save();

        return $profile->fresh();
    }

    /**
     * @param  list<string>  $slugs
     */
    private function assertValidTraits(array $slugs, TraitType $type): void
    {
        if ($slugs === []) {
            return;
        }

        $valid = TasteTrait::query()
            ->active()
            ->where('type', $type)
            ->whereIn('slug', $slugs)
            ->pluck('slug')
            ->all();

        $invalid = array_diff($slugs, $valid);
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                $type->value => [__('validation.invalid_traits', ['traits' => implode(', ', $invalid)])],
            ]);
        }
    }
}
