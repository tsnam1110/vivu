<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PremiumSource;
use App\Enums\PremiumSubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'starts_at',
        'ends_at',
        'status',
        'source',
        'notes',
        'granted_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => PremiumSubscriptionStatus::class,
            'source' => PremiumSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'granted_by_admin_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PremiumSubscriptionStatus::Active);
    }

    public function isCurrentlyValid(): bool
    {
        if ($this->status !== PremiumSubscriptionStatus::Active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // null ends_at = lifetime
        return $this->ends_at === null || $this->ends_at->isFuture();
    }
}
