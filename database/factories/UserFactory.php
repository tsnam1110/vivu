<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'username' => Str::lower(Str::slug(fake()->unique()->userName(), '_')),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_path' => null,
            'sample_avatar_id' => null,
            'avatar_frame_id' => null,
            'premium_expires_at' => null,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function withPremiumAvatar(): static
    {
        return $this->state(fn () => [
            'premium_expires_at' => now()->addYear(),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            UserProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'personality' => [],
                    'interests' => [],
                    'is_matchable' => true,
                ],
            );
            $user->unsetRelation('profile');
        });
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => UserStatus::Suspended]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
