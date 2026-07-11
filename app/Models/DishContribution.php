<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DishContribution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dish_id',
        'user_id',
        'type',
        'payload',
        'status',
        'is_canonical',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContributionType::class,
            'payload' => 'array',
            'status' => ContributionStatus::class,
            'is_canonical' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ContributionStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ContributionStatus::Pending);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'payload' => $this->payload,
            'is_canonical' => $this->is_canonical,
            'status' => $this->status->value,
            'author' => $this->user
                ? [
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                ]
                : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
