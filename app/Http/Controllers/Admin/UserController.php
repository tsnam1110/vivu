<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PremiumSource;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GrantUserPremiumRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\PremiumSubscriptionResource;
use App\Models\User;
use App\Services\PremiumSubscriptionService;
use App\Support\AdminDateRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly PremiumSubscriptionService $premiumService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()
            ->with(['profile', 'avatarFrame', 'sampleAvatar'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('premium')) {
            if ($request->string('premium') === 'active') {
                $query->whereNotNull('premium_expires_at')->where('premium_expires_at', '>', now());
            } elseif ($request->string('premium') === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('premium_expires_at')->orWhere('premium_expires_at', '<=', now());
                });
            }
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        AdminDateRange::apply($query, $request);

        return AdminUserResource::collection(
            $query->paginate(min((int) $request->integer('per_page', 15), 50))
        );
    }

    public function update(UpdateUserStatusRequest $request, User $user): AdminUserResource
    {
        $user->update(['status' => UserStatus::from($request->validated('status'))]);

        return new AdminUserResource($user->fresh(['profile', 'avatarFrame', 'sampleAvatar']));
    }

    public function grantPremium(GrantUserPremiumRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $days = ! empty($data['lifetime']) ? null : (int) ($data['days'] ?? 30);

        $sub = $this->premiumService->grant(
            $user,
            $days,
            PremiumSource::Admin,
            $data['notes'] ?? null,
            $request->user('admin'),
        );

        return (new PremiumSubscriptionResource($sub->load(['user', 'grantedByAdmin'])))
            ->response()
            ->setStatusCode(201);
    }
}
