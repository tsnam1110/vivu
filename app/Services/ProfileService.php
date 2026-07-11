<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PremiumSource;
use App\Enums\TraitType;
use App\Models\AvatarFrame;
use App\Models\SampleAvatar;
use App\Models\TasteTrait;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class ProfileService
{
    public function __construct(
        private readonly PremiumSubscriptionService $premiumService,
    ) {}

    /**
     * @param  array{
     *   name?: string,
     *   username?: string,
     *   avatar?: UploadedFile|null,
     *   sample_avatar_id?: int|null,
     *   avatar_frame_id?: int|null,
     *   remove_avatar?: bool
     * }  $data
     */
    public function updateAccount(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['username'])) {
                $user->username = strtolower($data['username']);
            }

            if (! empty($data['remove_avatar'])) {
                $this->deleteAvatarFile($user);
                $user->avatar_path = null;
                $user->sample_avatar_id = null;
            }

            if (($data['avatar'] ?? null) instanceof UploadedFile) {
                $this->storeAvatar($user, $data['avatar']);
                $user->sample_avatar_id = null;
            } elseif (array_key_exists('sample_avatar_id', $data) && $data['sample_avatar_id'] !== null) {
                $this->applySampleAvatar($user, (int) $data['sample_avatar_id']);
            }

            if (array_key_exists('avatar_frame_id', $data)) {
                $this->applyFrame($user, $data['avatar_frame_id'] !== null && $data['avatar_frame_id'] !== ''
                    ? (int) $data['avatar_frame_id']
                    : null);
            }

            $user->save();

            return $user->fresh(['profile', 'avatarFrame', 'sampleAvatar']) ?? $user;
        });
    }

    /**
     * Demo unlock Premium (v1 — payment gateway later).
     */
    public function enablePremiumDemo(User $user, int $days = 30): User
    {
        $this->premiumService->grant(
            $user,
            days: $days,
            source: PremiumSource::Demo,
            notes: 'Self-service demo unlock',
        );

        return $user->fresh(['profile', 'avatarFrame', 'sampleAvatar']) ?? $user;
    }

    /**
     * Change password (current password already validated by Form Request).
     * Plain value is hashed via User model cast.
     */
    public function updatePassword(User $user, string $newPassword): User
    {
        $user->forceFill([
            'password' => $newPassword,
        ])->save();

        return $user->fresh() ?? $user;
    }

    private function applySampleAvatar(User $user, int $sampleId): void
    {
        $sample = SampleAvatar::query()->active()->whereKey($sampleId)->first();
        if (! $sample) {
            throw ValidationException::withMessages([
                'sample_avatar_id' => [__('messages.sample_avatar_invalid')],
            ]);
        }

        $this->deleteAvatarFile($user);
        $user->avatar_path = null;
        $user->sample_avatar_id = $sample->id;
    }

    private function applyFrame(User $user, ?int $frameId): void
    {
        if ($frameId === null) {
            $user->avatar_frame_id = null;

            return;
        }

        $frame = AvatarFrame::query()->active()->whereKey($frameId)->first();
        if (! $frame) {
            throw ValidationException::withMessages([
                'avatar_frame_id' => [__('messages.avatar_frame_invalid')],
            ]);
        }

        if ($frame->is_premium && ! $user->hasActivePremium()) {
            throw ValidationException::withMessages([
                'avatar_frame_id' => [__('messages.premium_avatar_required')],
            ]);
        }

        $user->avatar_frame_id = $frame->id;
    }

    private function storeAvatar(User $user, UploadedFile $file): void
    {
        $this->deleteAvatarFile($user);

        $dir = 'avatars/'.$user->id;
        $filename = 'avatar_'.time().'.jpg';
        $path = $dir.'/'.$filename;

        $encoded = Image::decode($file)
            ->cover(400, 400)
            ->encodeUsingFormat(Format::JPEG, quality: 85);

        Storage::disk('public')->put($path, (string) $encoded);
        $user->avatar_path = $path;
    }

    private function deleteAvatarFile(User $user): void
    {
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }

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
