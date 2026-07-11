<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    /**
     * @param  array{email: string, password: string}  $credentials
     * @return array{admin: Admin, token: string}
     */
    public function login(array $credentials): array
    {
        $admin = Admin::query()->where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $admin->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        $admin->forceFill(['last_login_at' => now()])->save();
        $token = $admin->createToken('admin-spa')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }

    public function logout(Admin $admin): void
    {
        $admin->currentAccessToken()?->delete();
    }
}
