<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PremiumSource;
use App\Enums\PremiumSubscriptionStatus;
use App\Models\Admin;
use App\Models\PremiumSubscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class PremiumSubscriptionService
{
    /**
     * Grant or extend Premium. Stacks from max(now, current expiry).
     *
     * @param  int|null  $days  null = lifetime
     */
    public function grant(
        User $user,
        ?int $days = 30,
        PremiumSource $source = PremiumSource::Admin,
        ?string $notes = null,
        ?Admin $admin = null,
    ): PremiumSubscription {
        return DB::transaction(function () use ($user, $days, $source, $notes, $admin) {
            $startsAt = now();
            $base = $user->premium_expires_at && $user->premium_expires_at->isFuture()
                ? $user->premium_expires_at->copy()
                : $startsAt->copy();

            $endsAt = $days === null ? null : $base->copy()->addDays(max(1, $days));

            $sub = PremiumSubscription::query()->create([
                'user_id' => $user->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => PremiumSubscriptionStatus::Active,
                'source' => $source,
                'notes' => $notes,
                'granted_by_admin_id' => $admin?->id,
            ]);

            $this->syncUserPremiumExpiry($user);

            return $sub->fresh(['user', 'grantedByAdmin']) ?? $sub;
        });
    }

    public function cancel(PremiumSubscription $subscription, ?string $notes = null): PremiumSubscription
    {
        return DB::transaction(function () use ($subscription, $notes) {
            $subscription->status = PremiumSubscriptionStatus::Cancelled;
            if ($notes !== null && $notes !== '') {
                $subscription->notes = trim(($subscription->notes ? $subscription->notes.' | ' : '').$notes);
            }
            $subscription->save();

            $this->syncUserPremiumExpiry($subscription->user ?? User::query()->findOrFail($subscription->user_id));

            return $subscription->fresh(['user', 'grantedByAdmin']) ?? $subscription;
        });
    }

    /**
     * Extend an active subscription by days (or set lifetime if days null).
     */
    public function extend(PremiumSubscription $subscription, ?int $days): PremiumSubscription
    {
        return DB::transaction(function () use ($subscription, $days) {
            if ($subscription->status !== PremiumSubscriptionStatus::Active) {
                $subscription->status = PremiumSubscriptionStatus::Active;
            }

            if ($days === null) {
                $subscription->ends_at = null;
            } else {
                $base = $subscription->ends_at && $subscription->ends_at->isFuture()
                    ? $subscription->ends_at->copy()
                    : now();
                $subscription->ends_at = $base->addDays(max(1, $days));
            }

            $subscription->save();
            $this->syncUserPremiumExpiry($subscription->user ?? User::query()->findOrFail($subscription->user_id));

            return $subscription->fresh(['user', 'grantedByAdmin']) ?? $subscription;
        });
    }

    /**
     * Recompute users.premium_expires_at from active subscriptions.
     * Lifetime (null ends_at) → far-future timestamp for simple comparisons.
     */
    public function syncUserPremiumExpiry(User $user): void
    {
        $subs = PremiumSubscription::query()
            ->where('user_id', $user->id)
            ->active()
            ->get();

        $expiry = null;
        $hasLifetime = false;

        foreach ($subs as $sub) {
            if (! $sub->isCurrentlyValid() && $sub->ends_at !== null && $sub->ends_at->isPast()) {
                continue;
            }

            if ($sub->status !== PremiumSubscriptionStatus::Active) {
                continue;
            }

            if ($sub->ends_at === null) {
                $hasLifetime = true;
                break;
            }

            if ($sub->ends_at->isFuture()) {
                $expiry = $expiry === null || $sub->ends_at->gt($expiry)
                    ? $sub->ends_at->copy()
                    : $expiry;
            }
        }

        if ($hasLifetime) {
            // Far future (safe for MySQL DATETIME; avoid TIMESTAMP year-2038 limit)
            $user->forceFill(['premium_expires_at' => now()->addYears(50)])->save();
        } else {
            $user->forceFill(['premium_expires_at' => $expiry])->save();
        }

        // Demote premium frame if no longer eligible
        if (! $user->fresh()?->hasActivePremium()) {
            $frame = $user->avatarFrame;
            if ($frame && $frame->is_premium) {
                $user->forceFill(['avatar_frame_id' => null])->save();
            }
        }
    }

    public function markExpiredIfNeeded(User $user): void
    {
        if ($user->premium_expires_at && $user->premium_expires_at->isPast()) {
            PremiumSubscription::query()
                ->where('user_id', $user->id)
                ->active()
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now())
                ->update(['status' => PremiumSubscriptionStatus::Expired->value]);

            $this->syncUserPremiumExpiry($user);
        }
    }

    public function setExactWindow(
        User $user,
        CarbonInterface $startsAt,
        ?CarbonInterface $endsAt,
        PremiumSource $source = PremiumSource::Admin,
        ?string $notes = null,
        ?Admin $admin = null,
    ): PremiumSubscription {
        return DB::transaction(function () use ($user, $startsAt, $endsAt, $source, $notes, $admin) {
            $sub = PremiumSubscription::query()->create([
                'user_id' => $user->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => PremiumSubscriptionStatus::Active,
                'source' => $source,
                'notes' => $notes,
                'granted_by_admin_id' => $admin?->id,
            ]);

            $this->syncUserPremiumExpiry($user);

            return $sub->fresh(['user', 'grantedByAdmin']) ?? $sub;
        });
    }
}
