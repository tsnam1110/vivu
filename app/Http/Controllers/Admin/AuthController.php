<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Resources\AdminResource;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AdminAuthService $service) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated());

        return response()->json([
            'data' => [
                'token' => $result['token'],
                'admin' => new AdminResource($result['admin']->load('roles')),
            ],
        ]);
    }

    public function logout(Request $request): Response
    {
        $this->service->logout($request->user('admin'));

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new AdminResource($request->user('admin')->load('roles')),
        ]);
    }
}
