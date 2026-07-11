<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar_path',
        'sample_avatar_id',
        'avatar_frame_id',
        'premium_expires_at',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'premium_expires_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function premiumSubscriptions(): HasMany
    {
        return $this->hasMany(PremiumSubscription::class);
    }

    public function sampleAvatar(): BelongsTo
    {
        return $this->belongsTo(SampleAvatar::class);
    }

    public function avatarFrame(): BelongsTo
    {
        return $this->belongsTo(AvatarFrame::class, 'avatar_frame_id');
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function hasActivePremium(): bool
    {
        return $this->premium_expires_at !== null && $this->premium_expires_at->isFuture();
    }

    /** @deprecated Use hasActivePremium() */
    public function getHasPremiumAvatarAttribute(): bool
    {
        return $this->hasActivePremium();
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar_path) {
            return asset('storage/'.$this->avatar_path);
        }

        if ($this->relationLoaded('sampleAvatar') && $this->sampleAvatar) {
            return $this->sampleAvatar->url();
        }

        if ($this->sample_avatar_id) {
            $sample = $this->sampleAvatar ?? SampleAvatar::query()->find($this->sample_avatar_id);

            return $sample?->url();
        }

        return null;
    }

    /**
     * Frame that should actually render (premium frames require active Premium).
     */
    public function resolvedAvatarFrame(): ?AvatarFrame
    {
        $frame = $this->relationLoaded('avatarFrame')
            ? $this->avatarFrame
            : ($this->avatar_frame_id ? $this->avatarFrame()->first() : null);

        if (! $frame || ! $frame->is_active) {
            return null;
        }

        if ($frame->is_premium && ! $this->hasActivePremium()) {
            return null;
        }

        return $frame;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : '?';
    }
}
