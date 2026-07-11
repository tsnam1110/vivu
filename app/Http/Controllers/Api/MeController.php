<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\MeResource;
use App\Http\Resources\UserProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function show(Request $request): MeResource
    {
        $user = $request->user('web')->load('profile');

        return new MeResource($user);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->profileService->updateProfile(
            $request->user('web'),
            $request->validated(),
        );

        return response()->json([
            'data' => new UserProfileResource($profile),
        ]);
    }
}
