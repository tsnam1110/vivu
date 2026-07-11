<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TagStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tag extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'usage_count',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
            'status' => TagStatus::class,
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TagStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TagStatus::Pending);
    }

    /**
     * Thẻ public (đã duyệt) + thẻ chờ duyệt do chính user tạo.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('status', TagStatus::Approved);
            if ($user) {
                $q->orWhere(function (Builder $inner) use ($user) {
                    $inner->where('status', TagStatus::Pending)
                        ->where('created_by', $user->id);
                });
            }
        });
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->status === TagStatus::Approved) {
            return true;
        }

        return $this->status === TagStatus::Pending
            && $user
            && (int) $this->created_by === (int) $user->id;
    }

    public function isPending(): bool
    {
        return $this->status === TagStatus::Pending;
    }
}
