<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAuthService
{
    /**
     * @param  array{name: string, username: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => UserStatus::Active,
            ]);

            UserProfile::query()->create([
                'user_id' => $user->id,
                'personality' => [],
                'interests' => [],
                'is_matchable' => true,
            ]);

            event(new Registered($user));
            Auth::guard('web')->login($user);

            return $user;
        });
    }

    /**
     * @param  array{email: string, password: string, remember?: bool}  $credentials
     */
    public function login(array $credentials): User
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! password_verify($credentials['password'], $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => [__('auth.suspended')],
            ]);
        }

        Auth::guard('web')->login($user, (bool) ($credentials['remember'] ?? false));
        request()->session()->regenerate();

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
