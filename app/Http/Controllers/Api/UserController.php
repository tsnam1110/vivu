<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function show(string $username): JsonResource
    {
        $user = User::query()
            ->where('username', $username)
            ->where('status', 'active')
            ->with('profile')
            ->firstOrFail();

        return new UserResource($user);
    }
}
