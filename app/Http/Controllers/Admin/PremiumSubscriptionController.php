<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PremiumSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePremiumSubscriptionRequest;
use App\Http\Requests\Admin\UpdatePremiumSubscriptionRequest;
use App\Http\Resources\PremiumSubscriptionResource;
use App\Models\PremiumSubscription;
use App\Models\User;
use App\Services\PremiumSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PremiumSubscriptionController extends Controller
{
    public function __construct(private readonly PremiumSubscriptionService $premiumService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PremiumSubscription::query()
            ->with(['user', 'grantedByAdmin'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->whereHas('user', function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return PremiumSubscriptionResource::collection(
            $query->paginate(min((int) $request->integer('per_page', 15), 50))
        );
    }

    public function store(StorePremiumSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = isset($data['user_id'])
            ? User::query()->findOrFail($data['user_id'])
            : User::query()->where('username', $data['username'])->firstOrFail();

        $admin = $request->user('admin');

        if (! empty($data['starts_at']) || ! empty($data['ends_at'])) {
            $sub = $this->premiumService->setExactWindow(
                $user,
                isset($data['starts_at']) ? \Carbon\Carbon::parse($data['starts_at']) : now(),
                ! empty($data['lifetime']) ? null : (isset($data['ends_at']) ? \Carbon\Carbon::parse($data['ends_at']) : null),
                PremiumSource::Admin,
                $data['notes'] ?? null,
                $admin,
            );
        } else {
            $days = ! empty($data['lifetime']) ? null : (int) ($data['days'] ?? 30);
            $sub = $this->premiumService->grant(
                $user,
                $days,
                PremiumSource::Admin,
                $data['notes'] ?? null,
                $admin,
            );
        }

        return (new PremiumSubscriptionResource($sub->load(['user', 'grantedByAdmin'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdatePremiumSubscriptionRequest $request,
        PremiumSubscription $premiumSubscription,
    ): PremiumSubscriptionResource {
        $data = $request->validated();

        if ($data['action'] === 'cancel') {
            $sub = $this->premiumService->cancel($premiumSubscription, $data['notes'] ?? null);
        } else {
            $days = ! empty($data['lifetime']) ? null : (int) ($data['days'] ?? 30);
            $sub = $this->premiumService->extend($premiumSubscription, $days);
        }

        return new PremiumSubscriptionResource($sub->load(['user', 'grantedByAdmin']));
    }
}
